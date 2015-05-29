Nemrod demo project
========================

This project is based on a [Symfony][1] Standard Edition and allows to quickly set a demonstration environment for the
[Nemrod framework][2]

1) Requirements
----------------------------------

- PHP > 5.3 with php-curl extension enabled
- Java Runtime Environment (JRE) 7

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

### Setting up a triple store and put data into it

We recommend [Blazegraph][4], a full open-source high-performance graph database. You first need to download a bundled version of Blazegraph. It can be done [here][6], or using curl with the following command:

    curl -L http://sourceforge.net/projects/bigdata/files/bigdata/1.5.0/bigdata-bundled.jar/download > bigdata-bundled.jar

You can then launch it with the following command:

    java -server -Xmx4g -jar bigdata-bundled.jar
    
You will probably need to adjust the maximm allocation pool parameter. Try for example -Xmx2g or -Xmx1g if you get an error with -Xmx4g. 

The demo project is built over a set of data describing [Nobel Prize laureates][5]. Load it into Blazegraph using:

    curl -X POST --data-binary "uri=http://data.nobelprize.org/dump.nt" http://localhost:9999/bigdata/sparql

### (optionnal) setting an Elasticsearch server

[Elasticsearch][7] is a search engine based on Lucene. Nemrod is able to populate an Elasticsearch index in order to make searches 
and list displays way faster. Follow these few steps if you want to try it on the demo.

You first need to download the current version of ElasticSearch. It can be done [here](https://www.elastic.co/downloads/elasticsearch). You just have to follow
the installation steps. Once the server is running, populate the Elasticsearch engine with the Nobel Prize data:

    php app/console nemrod:elastica:populate

(this operation may take a couple of minutes depending on your computer). Now the Elasticsearch section is available.

# The project is ready to be tested now !

Try it at the url that was given when launching the server. For now, you can
 
 - browse the nobel prize by years / categories
 - add a new nobel prize.


[1]: http://symfony.com/doc/2.4/book/installation.html
[2]: https://github.com/conjecto/nemrod
[3]: http://getcomposer.org/
[4]: http://www.blazegraph.com/
[5]: http://datahub.io/dataset/nobelprizes
[6]: http://sourceforge.net/projects/bigdata/files/bigdata/1.5.0/bigdata-bundled.jar/download
[7]: https://www.elastic.co/products/elasticsearch
