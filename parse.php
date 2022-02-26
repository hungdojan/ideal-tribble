<?php

// definovane regularni vyrazy
$FLOAT_REGEX  = 'float@\d*\.\d+';
$VAR_REGEX    = 'v{4}@[a-zA-Z_][a-zA-Z0-9_]*';
$DEFVAR_REGEX = "/^DEFVAR $VAR_REGEX\s*$/";
$MOVE_REGEX   = "/^MOVE $VAR_REGEX ($VAR_REGEX|$FLOAT_REGEX)\s*$/";
$ADD_REGEX    = "/^ADD $VAR_REGEX ($VAR_REGEX|$FLOAT_REGEX) ($VAR_REGEX|$FLOAT_REGEX)\s*$/";
$WRITE_REGEX  = "/^WRITE ($VAR_REGEX|$FLOAT_REGEX)\s*$/";
$VALID_OPERATIONS = ['WRITE', 'MOVE', 'ADD', 'DEFVAR'];

function get_argument($data)
{
    // vsechny argumenty v radku
    // $data[0] je jmeno instrukce
    $arg = [];
    if (count($data) > 1)
    {
        // rozdeleni podle definovaneho oddelovace
        // na prvnim indexu je typ argumentu (vvvv nebo float)
        // vvvv zmenime na var pro generovani xml
        $splited_line = explode("@", $data[1]);
        if ($splited_line[0] == 'vvvv')
            $splited_line[0] = 'var';
        // ulozime vyslednou dvojici [typ, hodnota] do pole $arg
        array_push($arg, $splited_line);
    }
    if (count($data) > 2)
    {
        $splited_line = explode("@", $data[2]);
        if ($splited_line[0] == 'vvvv')
            $splited_line[0] = 'var';
        array_push($arg, $splited_line);
    }
    if (count($data) > 3)
    {
        $splited_line = explode("@", $data[3]);
        if ($splited_line[0] == 'vvvv')
            $splited_line[0] = 'var';
        array_push($arg, $splited_line);
    }
    // vysledek vratime
    return $arg;
}

function create_operation($line)
{
    $input = trim($line);

    // zisk prikazu
    $ins_data = preg_split("/\s+/", $input);
    if (count($ins_data) > 0)
        $ins  = $ins_data[0];

    // nacteni argumentu
    $args = get_argument($ins_data);
    // vysledna struktura
    // tato struktura je velice neefektivni a spatne se cte
    // doporuceni je vytvoreni nejake tridy/struktury pro praci s tim
    // array(
    //     instrukce: $ins,
    //     argumenty: array(
    //         array(typ, hodnota),
    //         array(typ, hodnota),
    //         array(typ, hodnota)
    //     )
    // )
    return [$ins, $args];
}

function parse()
{
    global $FLOAT_REGEX, $VAR_REGEX, $DEFVAR_REGEX, $MOVE_REGEX, $ADD_REGEX, $WRITE_REGEX, $VALID_OPERATIONS;
    $valid_oper = false;
    $operations = [];
    $file = fopen("tests/parse/test_valid.src", "r");

    // pruchod souborem
    while (($line = fgets(STDIN)))
    {
        // kontrola kodu 20
        // prochazi vsemi povolenymi prikazy a kontroluje
        // zda radek zacina timto slovem
        foreach ($VALID_OPERATIONS as $oper_regex)
        {
            // v pripade nalezeni je pruchod ukoncen
            if (preg_match("/^$oper_regex\s/", $line))
            {
                $valid_oper = true;
                break;
            }
        }
        // pri nenalezeni se program ukonci kodem 20
        if (!$valid_oper)
            exit(20);

        $op = null;
        // kontrola kodu 21
        if (preg_match($DEFVAR_REGEX, $line))   // prikaz DEFVAR
        {
            array_push($operations, create_operation($line));
        }
        elseif (preg_match($MOVE_REGEX, $line))   // prikaz MOVE
        {
            array_push($operations, create_operation($line));
        }
        elseif (preg_match($WRITE_REGEX, $line))   // prikaz WRITE
        {
            array_push($operations, create_operation($line));
        }
        elseif (preg_match($ADD_REGEX, $line))   // prikaz ADD
        {
            array_push($operations, create_operation($line));
        }
        else
            exit(21);
    }
    // finalni struktura vypada takto (pro urychleni je vsechno ulozene v poli)
    // VELMI NEPREHLEDNE
    // $operation = array(
    //      array(instrukce, argumenty),
    //      array(instrukce, argumenty),
    //          ...
    //      array(instrukce, argumenty)
    // )
    fclose($file);
    return $operations;
}

function toXML($operations)
{
    $xw = xmlwriter_open_memory();
    xmlwriter_set_indent($xw, true);
    $res = xmlwriter_set_indent_string($xw, '  ');

    // header
    xmlwriter_start_document($xw, '1.0', 'UTF-8');

    // <program language="IPPcode22">
    xmlwriter_start_element($xw, 'program');
    xmlwriter_start_attribute($xw, 'language');
    xmlwriter_text($xw, 'MyLanguage');
    xmlwriter_end_attribute($xw);

    // prikad pristupu k druhemu argumentu treti operace
    // item[2] -> treti operace
    // item[2][1] -> list argumentu treti operace
    // item[2][1][1] -> druhy argument treti operace
    // peklo pro cteni
    foreach($operations as $index => $item)
    {
        // index+1 je pouzito pro vygenerovani ORDER
        // item je jedna instrukce (struktura viz nahore)
        // item[0] -> instrukce
        // item[1] -> list argumentu
        // item[1][0] -> prvni argument
        // item[1][1] -> druhy argument
        // item[1][1][0] -> typ druheho argumentu
        // item[1][1][1] -> hodnota druheho argumentu
        // -----------------------------------------------
        // <instruction>
        xmlwriter_start_element($xw, 'instruction');

        // <instruction order="XXX" opcode="XXX">
        xmlwriter_start_attribute($xw, 'order');
        xmlwriter_text($xw, $index+1);
        xmlwriter_end_attribute($xw);

        xmlwriter_start_attribute($xw, 'opcode');
        xmlwriter_text($xw, $item[0]);
        xmlwriter_end_attribute($xw);

        // <argX type="XXX">XXX</argX>
        foreach ($item[1] as $arg_index => $arg_data)
        {
            $arg_index += 1;
            $type = $arg_data[0];
            $value = $arg_data[1];

            // <argX>
            xmlwriter_start_element($xw, "arg$arg_index");

            // TODO: replace problematic characters (<, >, &)

            // <argX type="XXX">
            xmlwriter_start_attribute($xw, 'type');
            xmlwriter_text($xw, $type);
            xmlwriter_end_attribute($xw);

            // content
            xmlwriter_text($xw, $value);

            // </argX>
            xmlwriter_end_element($xw);
        }

        // </instruction>
        xmlwriter_end_element($xw);

    }

    // </program>
    xmlwriter_end_element($xw);
    echo xmlwriter_output_memory($xw);
}

// main
$operations = parse();
toXML($operations);
?>
