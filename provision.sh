# This script installs mailcatcher and requirements

#sudo yum update -y # update packages, seems unnecessary
curl -L get.rvm.io | bash -s stable
source /etc/profile.d/rvm.sh
rvm requirements
rvm install 1.9.3
rvm use 1.9.3 --default
rvm rubygems current
gem install mailcatcher
gem uninstall i18n --force # mailcatcher gem installs broken beta version of this, so remove it
gem install i18n # and install stable version
mailcatcher --ip=0.0.0.0 # on port 1080
