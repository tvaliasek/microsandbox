module.exports = function (grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        uglify: {
            default: {
                files: {
                    'www/js/front-libs.min.js': ['www/js/jquery-2.2.4.min.js', 'www/js/bootstrap/bootstrap.min.js', 'www/js/nette/netteForms.js', 'www/js/nette/nette.ajax.js', 'www/js/nette/extensions/confirm.ajax.js'],
                    'www/js/front.min.js': ['www/js/front.js']
                },
                mangle: false,
                quoteStyle: 3,
                screwIE8: true,
                preserveComments: false
            }
        },
        cssmin: {
            options: {
                roundingPrecision: -1,
                sourceMap: true
            },
            target: {
                files: {
                    'www/css/crit.min.css': [
                        'www/css/crit.css'
                    ],
                    'www/css/non-crit.min.css': [
                        'www/css/non-crit.css'
                    ]
                }
            }
        },
        compass: {
            default: {
                options: {
                    sassDir: 'www/scss',
                    cssDir: 'www/css',
                    force: true,
                    outputStyle: 'compact'
                }
            }
        }
    });
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-compass');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.registerTask('default', ['uglify:default', 'compass', 'cssmin']);

};
