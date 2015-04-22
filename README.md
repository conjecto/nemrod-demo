Nemrod demo project
========================

This project is based on a [Symfony][1] Standard Edition and allows to quickly set a demonstration environment for the
Nemrod framework

1) Requirements
----------------------------------

- PHP > 5.3 with php-curl extension enabled

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
    
The command will tell you te project's url. This is where the demo will be available at the end of the setup

### Setting up a triple store and put data into it

We recommend [Blazegraph][3], a full open-source high-performance graph database. You first need to download a bundled version of Blazegraph. It can be done [here][5], or using curl with the following command:

    curl -L http://sourceforge.net/projects/bigdata/files/bigdata/1.5.0/bigdata-bundled.jar/download > bigdata-bundled.jar

You can then launch it with the following command:

    java -server -Xmx4g -jar bigdata-bundled.jar
    
You will probably need to adjust the maximm allocation pool parameter. Try for example -Xmx2g or -Xmx1g if you get an error with -Xmx4g. 

The demo project is built over a set of data describing [Nobel Prize laureates][4]. Load it into Blazegraph using:

    curl -X POST --data-binary "uri=http://data.nobelprize.org/dump.nt" http://localhost:9999/bigdata/sparql

# The project is ready to be tested now !

Try it at the url that was given when launching the server. For now, you can
 
 - bowse the nobel prize by years / categories
 - add a new nobel prize.


[1]: http://symfony.com/doc/2.4/book/installation.html
[2]: http://getcomposer.org/
[3]: http://www.blazegraph.com/
[4]: http://datahub.io/dataset/nobelprizes
[5]: http://sourceforge.net/projects/bigdata/files/bigdata/1.5.0/bigdata-bundled.jar/download
