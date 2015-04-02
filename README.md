Nemrod demo project
========================

This project is based on a [Symfony][1] Standard Edition and allows to quickly set a demonstration environment for the
[Nemrod framework][2]

1) Requirements
----------------------------------

- PHP > 5.3

2) Installing the project
----------------------------------

### Cloning the project

    git clone http://github.com/conjecto/nemrod-demo

### Installing Composer

[Composer][3] is a tool for dependency management in PHP, used by Symfony and Nemrod.
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
    
The command will tell you te project's url. This is where the demo will be available at the end of the setup

### Setting up a bigdata server and put data into it

You first need to download a bundled version of [Blazegraph][4]. It can be done [here][6], or using curl with the following command:

    curl -L http://sourceforge.net/projects/bigdata/files/bigdata/1.5.0/bigdata-bundled.jar/download > bigdata-bundled.jar

The project comes with a bundled version of [Blazegraph][4]. You can launch it with the following command:

    java -server -Xmx4g -jar bigdata-bundled.jar
    
You will probably need to adjust the maximum allocation pool parameter. Try for example -Xmx2g or -Xmx1g if you get an error with -Xmx4g. 

The demo project is built over a set of data describing [Nobel Prize laureates][5]. Load it into Blazegraph using:

    curl -X POST --data-binary "uri=http://data.nobelprize.org/dump.nt" http://localhost:9999/bigdata/sparql

# The project is ready to be tested now !

Try it at the url that was given when launching the server. For now, you can
 
 - browse the nobel prize by years / categories
 - add a new nobel prize.


[1]:  http://symfony.com/doc/2.4/book/installation.html
[2]:  https://github.com/conjecto/nemrod
[3]:  http://getcomposer.org/
[4]:  http://www.blazegraph.com/
[5]:  http://datahub.io/dataset/nobelprizes
[6]: http://sourceforge.net/projects/bigdata/files/bigdata/1.5.0/bigdata-bundled.jar/download
