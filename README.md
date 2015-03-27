Nemrod demo project
========================

This project is based on a [Symfony][1] Standard Edition and allows to quickly set a demonstration environment for the
Nemrod framework

1) Requirements
----------------------------------

- PHP > 5.3

2) Installing the project
----------------------------------

### Cloning the project

    git clone http://github.com/conjecto/nemroddemo

### Installing Composer

[Composer][2] is a tool for dependency management in PHP, used by Symfony and Nemrod.
If you don't have Composer yet, download it following the instructions on
http://getcomposer.org/ or just run the following command:

    curl -s http://getcomposer.org/installer | php

### Installing the project

Then in the project directory, run

    php composer.phar install

Composer will install Nemrod, Symfony and all their dependencies into the directory.

### Running a web server

Run a web server allowing to access the symfony project with the following command:

    php app/console server:run
    
The command will tell te project's url.

### Setting up a bigdata server and put data into it

The project comes with a bundled version of [Blazegraph][3]. You can launch it with the following command:

    java -server -Xmx4g -jar bigdata-bundled.jar

A set of data concerning [Nobel Prize laureates][4] is provided with the project. Load it into Blazegraph using:

    curl -X POST --data-binary "uri=file:///c:/path/to/project/dir/app/Resources/data/dump.nt" http://localhost:9999/bigdata/sparql

# The project is ready to be tested now !

Try it at :



[1]:  http://symfony.com/doc/2.4/book/installation.html
[2]:  http://getcomposer.org/
[3]:  http://www.blazegraph.com/
[4]:  http://datahub.io/dataset/nobelprizes
