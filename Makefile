.PHONY: help
.DEFAULT_GOAL := help

help: ## Display this message
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

install: ## Install deps
	mysql -uroot -proot -e 'CREATE DATABASE IF NOT EXISTS `gulp-wp-theme`'
	npm set progress=false && npm install
	if [ ! -f dist/index.php ]; then make wordpress; fi;
	make build

build: ## Build assets
	rm -rf dist/wp-content/themes/modular-switch
	node_modules/.bin/gulp

watch: ## Watch asset changes
	make build
	node_modules/.bin/gulp watch

wordpress: ## Install wordpress
	cd dist && rm -rf wp-{a*,b*,comun*,cont*,cr*,config-s*,i*,l*,m*,s*,t*} xmlrpc.php in* lic* lie* rea* wor*
	curl -k https://de.wordpress.org/latest-de_DE.zip -o dist/wordpress.zip
	cd dist && unzip wordpress.zip
	cd dist && rm ./wordpress/wp-config-sample.php
	cd dist && mv -n ./wordpress/* .
	if [ ! -f dist/local-config.php ]; then php dist/local-config-generator.php; fi;
	cd dist && rm -rf wordpress readme.html liesmich.html license.txt wordpress.zip wp-content/themes/twenty*
	make db_import
	make wordpress_plugins_install
	find ./dist -name '__MACOSX' -exec rm -rv {} \; || true
	find ./dist -name '.DS_Store' -exec rm -v {} \; || true

wordpress_plugins_dump: ## Dump plugins
	find ./dist -name '__MACOSX' -exec rm -rv {} \; || true
	find ./dist -name '.DS_Store' -exec rm -v {} \; || true
	rm -rf ./resources/plugins/*.zip
	cd ./dist/wp-content/plugins && for d in ./*; do zip -rq ./../../../resources/plugins/$$d.zip $$d; done

wordpress_plugins_install: ## Install previously dumped plugins
	rm -rf ./dist/wp-content/plugins/*
	for f in ./resources/plugins/*.zip; do unzip -q $$f -d ./dist/wp-content/plugins; done || true

db_dump: ## Dump database
	wp db export /vagrant/sql/initial.sql --path="/vagrant/dist"

db_import: ## Import database
	wp db reset --yes --path="/vagrant/dist"
	wp db import /vagrant/sql/initial.sql --path="/vagrant/dist"
