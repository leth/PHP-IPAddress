#
# Cookbook Name:: lamp
# Recipe:: default
#
# Copyright 2011, Dave Widmer
#
# Licensed under the MIT license
#

# Package manager updates for debian systems
if platform?("debian", "ubuntu")
	require_recipe "apt"
	require_recipe "vim"
end

# Add in some recipes
require_recipe "apache2"
require_recipe "lamp::php"

if node[:lamp][:install][:xdebug]
	require_recipe "lamp::xdebug"
end

if node[:lamp][:install][:mcrypt]
	require_recipe "lamp::mcrypt"
end

if node[:lamp][:install][:webgrind]
	require_recipe "lamp::webgrind"
end


# Build the site
web_app node[:lamp][:server_name] do
	template "vhosts.conf.erb"
	server_name node[:lamp][:server_name]
	docroot node[:lamp][:docroot]
end

# Add in the php directory stuff
template "#{node[:apache][:dir]}/httpd.conf" do
	source "httpd.conf.erb"
	notifies :reload, resources(:service => "apache2"), :delayed
end
