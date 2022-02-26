import re
import sys
import xml.etree.ElementTree as ET

variables = {}

class Argument:
    def __init__(self, dtype, value):
        self.type = dtype
        # pretypovani na float pokud je argument konstanta
        self.value = float(value) if self.type == 'float' else value

    def __str__(self):
        return f"{self.type} => {self.value}"


def move(args):
    # kontrola, zda promenna existuje
    # pokud ne, tak vyhodi chybu 23
    if variables.get(args[0].value) is None:
        sys.exit(23)

    # pokud je druhy operand promenna
    # je potreba ji ziskat z tabulky promennych
    if args[1].type == 'var':
        # kontrola, zda promenna existuje
        # pokud ne, tak vyhodi chybu 23
        if variables.get(args[1].value) is None:
            sys.exit(23)
        value = variables.get(args[1].value)
    else:
        value = args[1].value
    
    # samotny prikaz: ulozeni hodnoty do promenne v args[0]
    variables[args[0].value] = value

def defvar(args):
    # kontrola, zda promenna existuje
    # pokud ano, tak vyhodi chybu 24
    if variables.get(args[0].value) is not None:
        sys.exit(24)
    variables[args[0].value] = 0.0

def add(args):
    # kontrola, zda promenna existuje
    # pokud ne, tak vyhodi chybu 23
    if variables.get(args[0].value) is None:
        sys.exit(23)

    # pokud je druhy operand promenna
    # je potreba ji ziskat z tabulky promennych
    if args[1].type == 'var':
        # kontrola, zda promenna existuje
        # pokud ne, tak vyhodi chybu 23
        if variables.get(args[1].value) is None:
            sys.exit(23)
        value1 = variables.get(args[1].value)
    else:
        value1 = args[1].value

    # pokud je druhy operand promenna
    # je potreba ji ziskat z tabulky promennych
    if args[2].type == 'var':
        # kontrola, zda promenna existuje
        # pokud ne, tak vyhodi chybu 23
        if variables.get(args[2].value) is None:
            sys.exit(23)
        value2 = variables.get(args[2].value)
    else:
        value2 = args[2].value

    # samotny prikaz: vypis hodnoty na stdout
    variables[args[0].value] = value1 + value2

def write(args):
    # pokud je druhy operand promenna
    # je potreba ji ziskat z tabulky promennych
    if args[0].type == 'var':
        # kontrola, zda promenna existuje
        # pokud ne, tak vyhodi chybu 23
        if variables.get(args[0].value) is None:
            sys.exit(23)
        value = variables.get(args[0].value)
    else:
        value = args[0].value

    # samotny prikaz: vypis hodnoty na stdout
    print(value, flush=True)

def xml_parse():
    tree = ET.parse(sys.stdin)
    # <program></program>
    root = tree.getroot()

    # list prikazu za sebou
    cmds = []

    # <instruction></instruction>
    # ziskani instruction z program elementu
    for command in root:
        opcode = command.get('opcode')
        args = []
        # <argX></argX>
        # ziskani argumentu z instruction elementu
        for arg in command:
            dtype = arg.get('type')
            value = arg.text
            args.append(Argument(dtype, value))
        cmds.append((opcode, args))

    # prikazy jsou ulozene v tuple strukture
    """
    popsana momentalni struktura
    [
        (
            index_0: jmeno instrukce,
            index_1: [
                arg1: Argument{
                    type: typ argumentu
                    value: hodnota argumentu
                },
                arg2: Argument{
                    type: typ argumentu
                    value: hodnota argumentu
                }
                arg3: Argument{
                    type: typ argumentu
                    value: hodnota argumentu
                }
            ]
        )
    ]
    """
    return cmds

def main():
    commands = xml_parse()
    for cmd in commands:
        if cmd[0] == 'DEFVAR':
            defvar(cmd[1])
        elif cmd[0] == 'MOVE':
            move(cmd[1])
        elif cmd[0] == 'WRITE':
            write(cmd[1])
        elif cmd[0] == 'ADD':
            add(cmd[1])

main()
