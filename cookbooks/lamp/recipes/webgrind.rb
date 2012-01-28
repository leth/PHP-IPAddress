#
# Cookbook Name:: lamp
# Recipe:: webgrind
#
# Copyright 2011, Dave Widmer
#
# Licensed under the MIT license
#
require_recipe "git"

# Install webgrind
git "#{node[:lamp][:webgrind][:dir]}" do
	repository "git://github.com/jokkedk/webgrind.git"
	reference "master"
	action :sync
end

template "#{node[:apache][:dir]}/conf.d/webgrind.conf" do
    source "directory.conf.erb"
    owner "root"
    group "root"
    mode 0644
    variables(
		:web_path => node[:lamp][:webgrind][:url],
		:full_path => node[:lamp][:webgrind][:dir]
    )
end