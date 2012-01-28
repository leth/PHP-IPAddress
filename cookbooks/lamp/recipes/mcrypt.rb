#
# Cookbook Name:: lamp
# Recipe:: mcrypt
#
# Copyright 2011, Dave Widmer
#
# Licensed under the MIT license
#

package "php5-mcrypt" do
	action :install
end

# Now fix mcrypt and comment problem
template "#{node[:php][:ext_conf_dir]}/mcrypt.ini" do
	owner "root"
	group "root"
	mode 0644
	source "mcrypt.ini.erb"
end
