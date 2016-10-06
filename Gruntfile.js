module.exports = function (grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        uglify: {
            default: {
                files: {
                    'www/js/front-libs.min.js': ['www/js/nette/netteForms.js', 'www/js/nette/nette.ajax.js', 'www/js/nette/extensions/confirm.ajax.js'],
                },
                mangle: false,
                quoteStyle: 3,
                screwIE8: true,
                preserveComments: false
            }
        }
    });
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.registerTask('default', ['uglify:default']);
    
};
