#!/usr/bin/env bash

NOF_TESTS=0
NOF_PASSED=0
# for dir in $(find "tests/interpret" -type f -name '*.src')
# do
#     # extract
#     FILEPATH=${dir%%.*}
#     echo "------------------------------------"
#     echo -n "Test $FILEPATH: "
# 
#     # prep arguments
#     ARGS=''
#     if [[ -f "./$FILEPATH.src" ]]
#     then
#         ARGS+=" --source=$FILEPATH.src"
#     fi
#     if [[ -f "./$FILEPATH.in" ]]
#     then
#         ARGS+=" --input=$FILEPATH.in"
#     fi
#     # run test
#     ((NOF_TESTS=NOF_TESTS+1))
#     python interpret.py < $FILEPATH.src > tmp/my_out 2> /dev/null
#     RC=$(echo $?)
#     EXP_RC=$(cat $FILEPATH.rc)
# 
#     # return code check
#     if [[ $RC -ne $EXP_RC ]]
#     then
#         echo "FAILED"
#         echo "Expected return value $EXP_RC; received $RC"
#         echo $FILEPATH >&2
#         continue
#     fi
#     if [[ $RC -ne 0 ]]
#     then
#         if [[ -s tmp/my_out ]]
#         then
#             echo "FAILED"
#             echo "Output file is not empty"
#             echo $FILEPATH >&2
#             continue
#         else
#             echo "OK"
#             ((NOF_PASSED=NOF_PASSED+1))
#             continue
#         fi
#     fi
#         
#     # xml check
#     # RES=$(java -jar ./jexamxml.jar tmp/my_out $FILEPATH.out tmp/diff.err ./options | tail -1)
#     if [[ -f "$FILEPATH.out" ]]
#     then
#         DIFF=$(diff $FILEPATH.out tmp/my_out)
#         # if [[ $RES = 'Two files are identical' ]]
#         if [[ $DIFF = '' ]]
#         then
#             ((NOF_PASSED=NOF_PASSED+1))
#             echo "OK"
#         else
#             echo "FAILED"
#             echo $FILEPATH >&2
#         fi
#     else
#         ((NOF_PASSED=NOF_PASSED+1))
#         echo "OK"
#     fi
#     rm -f tmp/*
# done

for dir in $(find "tests/parse/" -type f -name '*.src')
do
    # extract
    FILEPATH=${dir%%.*}
    echo "------------------------------------"
    echo -n "Test $FILEPATH: "
    # run test
    ((NOF_TESTS=NOF_TESTS+1))
    php parse.php < $FILEPATH.src > tmp/my_out 2> /dev/null
    RC=$(echo $?)
    EXP_RC=$(cat $FILEPATH.rc)

    # return code check
    if [[ $RC -ne $EXP_RC ]]
    then
        echo "FAILED"
        echo "Expected return value $EXP_RC; received $RC"
        echo $FILEPATH >&2
        continue
    fi
    if [[ $RC -ne 0 ]]
    then
        if [[ -s tmp/my_out ]]
        then
            echo "FAILED"
            echo "Output file is not empty"
            echo $FILEPATH >&2
            continue
        else
            echo "OK"
            ((NOF_PASSED=NOF_PASSED+1))
            continue
        fi
    fi

    # xml check
    RES=$(java -jar ./jexamxml.jar tmp/my_out $FILEPATH.out tmp/diff.err ./options | tail -1)
    if [[ $RES = 'Two files are identical' ]]
    then
        ((NOF_PASSED=NOF_PASSED+1))
        echo "OK"
    else
        echo "FAILED"
        echo $FILEPATH >&2
    fi
    rm -f tmp/*
done

echo "------------------------------------"
echo "Result: $NOF_PASSED/$NOF_TESTS" >&2
