# Demo implementace IPP projektu

## Instrukční sada
```
DEFVAR <var>
    definuje proměnnou, hodnota by default=0.0
MOVE <var> <symb>
    přemístí hodnotu <symb> do <var>
WRITE <var>
    vypíše na stdout hodnotu uloženou v proměnné <var>
ADD <var> <symb1> <symb2>
    uloží do proměnné <var> součet hodnot <symb1> a <symb2>
```

## Operandy
```
<var> Proměnná ve tvaru:
    prefix:     vvvv@
    hodnota:    vsechny alfanumericke znaky a podtrzitko
                    prvni znak nesmi byt cislo

<symb> Proměnná nebo konstanta
    prefix konstanty: float@
    hodnota konstanty: desetinne cele cislo ve tvaru
        cisla tecka cisla -> napr. 0.02, 200.8, 100.0, .123
```

## Chyby
- 20 - neznamý příkaz
- 21 - lexikální nebo syntaktická chyba
- 23 - nedefinovaná proměnná
- 24 - znovu definování již definované proměnné
