#
# Cookbook Name:: lamp
# Recipe:: php
#
# Copyright 2011, Dave Widmer
#
# Licensed under the MIT license
#

require_recipe "php"

# Debian systems need the php5 module added in...
if platform?("debian", "ubuntu")
	package "libapache2-mod-php5"
end
