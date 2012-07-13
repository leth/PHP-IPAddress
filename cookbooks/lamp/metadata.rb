maintainer       "Dave Widmer"
maintainer_email "dave@davewidmer.net"
license          "MIT"
description      "Installs a working LAMP server."
long_description IO.read(File.join(File.dirname(__FILE__), 'README.rdoc'))
version          "0.1.0"
recipe           "lamp", "Installs the base lamp server"
recipe           "lamp::mcrypt", "Installs php-mcrypt"
recipe           "lamp::php", "Installs the php cookbook with a few extra options"
recipe           "lamp::webgrind", "Installs webgrind"
recipe           "lamp::xdebug", "Installs the xdebug Zend module"

%w{ ubuntu debian }.each do |os|
  supports os
end

%w{ apache2, git, mysql, php }.each do |depend|
	depends depend
end

attribute "lamp/server_name",
  :display_name => "Virutal Host server name",
  :description => "The name you will use to access your lamp site",
  :default => "localhost"

attribute "lamp/docroot",
  :display_name => "The document root for your site",
  :description => "The full path (without ending slash) to your webroot",
  :default => "/vagrant/html"

attribute "lamp/webgrind/dir",
  :display_name => "The directory to install webgrind",
  :description => "The full path to install webgrind from git",
  :default => "/var/www/webgrind"

attribute "lamp/webgrind/url",
  :display_name => "The site url for webgrind",
  :description => "The url path you will put into your browswer to see webgrind",
  :default => "/webgrind"

attribute "lamp/xdebug/path",
  :display_name => "The full path to where the xdebug module will live",
  :description => "Since xdebug is installed as a zend_extension you need the full path to it",
  :default => "/usr/lib/php5/20090626+lfs/"

attribute "lamp/install/mcrypt",
  :display_name => "Install mcrypt?",
  :description => "Installation flag for mcrypt",
  :default => "true"

attribute "lamp/install/webgrind",
  :display_name => "Install webgrind?",
  :description => "Installation flag for webgrind",
  :default => "true"

attribute "lamp/install/xdebug",
  :display_name => "Install xdebug?",
  :description => "Installation flag for xdebug",
  :default => "true"
