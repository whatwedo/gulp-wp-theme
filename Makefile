install:
	@npm install
	@bower install

watch:
	@node_modules/gulp/bin/gulp.js
	@node_modules/gulp/bin/gulp.js watch

build:
	@node_modules/gulp/bin/gulp.js --env production

compile:
	@node_modules/gulp/bin/gulp.js

wordpress:
	@cd dist && rm -rf wp-{a*,b*,com*,cont*,cr*,config-s*,i*,l*,m*,s*,t*} xmlrpc.php in* lic* lie* rea* wor*
	@curl https://wordpress.org/latest.zip -o dist/wordpress.zip
	@cd dist && unzip wordpress.zip
	@cd dist && rm ./wordpress/wp-config-sample.php
	@cd dist && cp -Rfn ./wordpress/* .
	@if [ ! -a dist/local-config.php ]; then php dist/local-config-generator.php; fi;
	@cd dist && rm -rf wordpress readme.html liesmich.html license.txt wordpress.zip wp-content/themes/twenty*
