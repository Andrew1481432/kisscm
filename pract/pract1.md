# Практическое занятие №1. Введение, основы работы в командной строке

П.Н. Советов, РТУ МИРЭА

Научиться выполнять простые действия с файлами и каталогами в Linux из командной строки. Сравнить работу в командной строке Windows и Linux.

## Задача 1

Вывести отсортированный в алфавитном порядке список имен пользователей в файле passwd (вам понадобится grep).

### Ответ
`awk -F: '{print $1}' /etc/passwd | sort`

## Задача 2

Вывести данные /etc/protocols в отформатированном и отсортированном порядке для 5 наибольших портов, как показано в примере ниже:

#### Example print:
```
[root@localhost etc]# cat /etc/protocols ...
142 rohc
141 wesp
140 shim6
139 hip
138 manet
```

### Ответ
`awk -F " " '{print $2, $1}' /etc/protocols | tail -5 | sort -r`

## Задача 3

Написать программу banner средствами bash для вывода текстов, как в следующем примере (размер баннера должен меняться!):

#### Example print:
```
[root@localhost ~]# ./banner "Hello from RTU MIREA!"
+-----------------------+
| Hello from RTU MIREA! |
+-----------------------+
```

Перед отправкой решения проверьте его в ShellCheck на предупреждения.

### Ответ
````
#!/bin/bash

msg="| $* |"
edge=$(echo "$msg" | sed -e "s/^.//;s/.$//" | sed -r 's/./-/g')
echo "+$edge+"
echo "$msg"
echo "+$edge+"
````

## Задача 4

Написать программу для вывода всех идентификаторов (по правилам C/C++ или Java) в файле (без повторений).

Пример для hello.c:

```
h hello include int main n printf return stdio void world
```

### Ответ
`cat hello.c | grep -o '[a-zA-Z0-9_]*' | sort`

## Задача 5

Написать программу для регистрации пользовательской команды (правильные права доступа и копирование в /usr/local/bin).

Например, пусть программа называется reg:

```
./reg banner
```

В результате для banner задаются правильные права доступа и сам banner копируется в /usr/local/bin.

### Ответ
````
#!/bin/sh

DIR=/usr/local/bin/$1
cp $1 $DIR
chmod +x $DIR
````

## Задача 6

Написать программу для проверки наличия комментария в первой строке файлов с расширением c, js и py.

### Ответ
```#!/bin/bash
if [ $# -eq 0 ]; then
        echo "Укажите файл в аргументе формате .py или .js!"
        exit 1
fi

e=`echo "$1" | cut -d '.' -f 2`
if [ "$e" == "js" ]; then
        cat "$1" | head -n 1 | grep -q '[//][/*]' &&
        echo "Комментарий в первой строке найден!" || echo ""
elif [ "$e" == "py" ]; then
        cat "$1" | head -n 1 | grep -q '[#]' &&
        echo "Комментарий в первой строке найден!" || echo ""
else
    echo "Укажите файл с расширением py или js!"
fi`
````

## Задача 7

Написать программу для нахождения файлов-дубликатов (имеющих 1 или более копий содержимого) по заданному пути (и подкаталогам).

### Ответ
`find . ! -empty -type f -exec md5sum {} + | sort | uniq -w32 -dD`

## Задача 8

Написать программу, которая находит все файлы в данном каталоге с расширением, указанным в качестве аргумента и архивирует все эти файлы в архив tar.

### Ответ
````
#!/bin/sh

tar -cf archive.tar *."$1"
````

## Задача 9

Написать программу, которая заменяет в файле последовательности из 4 пробелов на символ табуляции. Входной и выходной файлы задаются аргументами.

### Ответ
````
#!/bin/sh

sed 's/    /\t/g' "$1" > "$2"
````

## Задача 10

Написать программу, которая выводит названия всех пустых текстовых файлов в указанной директории. Директория передается в программу параметром. 

### Ответ
````
#!/bin/sh

find $1 -maxdepth 1 -type f -empty -name "*.txt"
````

## Полезные ссылки

Линукс в браузере: https://bellard.org/jslinux/

ShellCheck: https://www.shellcheck.net/

Разработка CLI-приложений

Общие сведения

https://ru.wikipedia.org/wiki/Интерфейс_командной_строки
https://nullprogram.com/blog/2020/08/01/
https://habr.com/ru/post/150950/

Стандарты

https://www.gnu.org/prep/standards/standards.html#Command_002dLine-Interfaces
https://www.gnu.org/software/libc/manual/html_node/Argument-Syntax.html
https://pubs.opengroup.org/onlinepubs/9699919799/basedefs/V1_chap12.html

Реализация разбора опций

Питон

https://docs.python.org/3/library/argparse.html#module-argparse
https://click.palletsprojects.com/en/7.x/