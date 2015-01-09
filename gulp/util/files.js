var path = require('path');
var gs = require('glob-stream');
var through = require('through2');

// Returns a stream for given globbing scheme
module.exports = function(dir, format) {
  return gs.create(dir)
    .pipe(through.obj(function(file, enc, callback) {
      this.push(format(path.basename(file.path)));
      callback();
    }));
};
