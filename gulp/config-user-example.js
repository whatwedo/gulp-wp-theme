/* This is an exmaple of a user config which is for use in your local environment only.
*  You can rename this to config-user.js and the parameters of the dev config will be overridden.
*  The config-user.js will not be added to the repository since it's not for your team.
*
*  In this example we configured browsersync to point to our local Apache Server. You can overwrite any
*  Parameter from the dev config.
*/

var packageConfig = require('../package.json');
var dest = './dist/wp-content/themes/' + packageConfig.name;
var src = './src';

module.exports = {
  browserSync: {
    server: false,
    files: [
      dest + "/**",
      "!" + dest + "/**.map"
    ],
    proxy: "myapp.dev",
    open: false
  }
};
