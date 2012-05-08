# Concrete5 Burrito

This is a group of libraries, classes, and other tools that greatly increase the speed at which we can create custom elements for Concrete5 projects. It also changes the Dashboard background to a subtle dark texture rather than the daily feed of images. 

## Installation

From your project's root directory

    $ git submodule add git://github.com/trivera/burrito.git packages/burrito
    $ git submodule init

Then, visit **yoursite.com/index.php/dashboard/extend/install/** and install the package. Once the package is installed, you can begin consuming the Burrito.

## CLI

Burrito includes a helper for command line oriented tasks.  To install it:

    $ cd packages/burrito
    $ bin/burrito install cli

This copies `burrito` to `/usr/local/bin`.

## Documentation / Usage

From any directory within your concrete5 project, run

   $ burrito help