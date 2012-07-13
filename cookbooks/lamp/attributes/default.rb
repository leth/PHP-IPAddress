#
# Cookbook Name:: lamp
# Attributes:: lamp
#
# Copyright 2011, Dave Widmer
#
# Licensed under the MIT license
#

default[:lamp][:server_name] = "localhost"
default[:lamp][:docroot] = "/vagrant/html"
default[:lamp][:xdebug][:path] = "/usr/lib/php5/20090626+lfs/"
default[:lamp][:webgrind][:dir] = "/var/www/webgrind"
default[:lamp][:webgrind][:url] = "/webgrind"

# Installation flags
default[:lamp][:install][:xdebug] = true
default[:lamp][:install][:mcrypt] = true
default[:lamp][:install][:webgrind] = true