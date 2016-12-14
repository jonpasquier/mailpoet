<?php

class RoboFile extends \Robo\Tasks {

  function install() {
    return $this->taskExecStack()
      ->stopOnFail()
      ->exec('./composer.phar install')
      ->exec('npm install')
      ->run();
  }

  function update() {
    $this->say(getenv('WP_TEST_URL'));

    return $this->taskExecStack()
      ->stopOnFail()
      ->exec('./composer.phar update')
      ->exec('npm update')
      ->run();
  }

  protected function rsearch($folder, $extensions = array()) {
    $dir = new RecursiveDirectoryIterator($folder);
    $iterator = new RecursiveIteratorIterator($dir);

    $pattern = '/^.+\.('.join($extensions, '|').')$/i';

    $files = new RegexIterator(
      $iterator,
      $pattern,
      RecursiveRegexIterator::GET_MATCH
    );

    $list = array();
    foreach($files as $file) {
      $list[] = $file[0];
    }

    return $list;
  }

  function watch() {
    $css_files = $this->rsearch('assets/css/src/', array('styl'));
    $js_files = $this->rsearch('assets/js/src/', array('js', 'jsx'));

    $this->taskWatch()
      ->monitor($js_files, function() {
        $this->compileJs();
      })
      ->monitor($css_files, function() {
        $this->compileCss();
      })
      ->run();
  }

  function watchCss() {
    $css_files = $this->rsearch('assets/css/src/', array('styl'));
    $this->taskWatch()
      ->monitor($css_files, function() {
        $this->compileCss();
      })
      ->run();
  }

  function watchJs() {
    $this->_exec('./node_modules/webpack/bin/webpack.js --watch');
  }

  function compileAll() {
    $collection = $this->collection();
    $collection->add(array($this, 'compileJs'));
    $collection->add(array($this, 'compileCss'));
    return $collection->run();
  }

  function compileJs() {
    return $this->_exec('./node_modules/webpack/bin/webpack.js --bail');
  }

  function compileCss() {
    $css_files = array(
      'assets/css/src/admin.styl',
      'assets/css/src/newsletter_editor/newsletter_editor.styl',
      'assets/css/src/public.styl',
      'assets/css/src/rtl.styl',
      'assets/css/src/importExport.styl'
    );

    return $this->_exec(join(' ', array(
      './node_modules/stylus/bin/stylus',
      '--include ./node_modules',
      '--include-css',
      '-u nib',
      join(' ', $css_files),
      '-o assets/css/'
    )));
  }

  function makepot() {
    return $this->_exec('./node_modules/.bin/grunt makepot'.
      ' --gruntfile='.__DIR__.'/tasks/makepot/makepot.js'.
      ' --base_path='.__DIR__
    );
  }

  function testUnit($opts=['file' => null, 'xml' => false]) {
    $this->loadEnv();
    $this->_exec('vendor/bin/codecept build');

    $command = 'vendor/bin/codecept run unit -f '.(($opts['file']) ? $opts['file'] : '');

    if($opts['xml']) {
      $command .= ' --xml';
    }
    return $this->_exec($command);
  }

  function testCoverage($opts=['file' => null, 'xml' => false]) {
    $this->loadEnv();
    $this->_exec('vendor/bin/codecept build');
    $command = join(' ', array(
      'vendor/bin/codecept run',
      (($opts['file']) ? $opts['file'] : ''),
      '--coverage',
      ($opts['xml']) ? '--coverage-xml' : '--coverage-html'
    ));

    if($opts['xml']) {
      $command .= ' --xml';
    }
    return $this->_exec($command);
  }

  function testJavascript($xml_output_file = null) {
    $this->compileJs();

    $command = join(' ', array(
      './node_modules/.bin/mocha',
      '-r tests/javascript/mochaTestHelper.js',
      'tests/javascript/testBundles/**/*.js'
    ));

    if(!empty($xml_output_file)) {
      $command .= sprintf(
        ' --reporter xunit --reporter-options output="%s"',
        $xml_output_file
      );
    }

    return $this->_exec($command);
  }

  function testDebug() {
    $this->_exec('vendor/bin/codecept build');
    $this->loadEnv();
    return $this->_exec('vendor/bin/codecept run unit --debug');
  }

  function testFailed() {
    $this->loadEnv();
    $this->_exec('vendor/bin/codecept build');
    return $this->_exec('vendor/bin/codecept run -g failed');
  }

  function qa() {
    $collection = $this->collection();
    $collection->add(array($this, 'qaLint'));
    $collection->add(function() {
      return $this->qaCodeSniffer('all');
    });
    return $collection->run();
  }

  function qaLint() {
    return $this->_exec('./tasks/php_lint.sh lib/ tests/ mailpoet.php');
  }

  function qaCodeSniffer($severity='errors') {
    if ($severity === 'all') {
      $severityFlag = '-w';
    } else {
      $severityFlag = '-n';
    }
    return $this->_exec(
      './vendor/bin/phpcs '.
      '--standard=./tasks/code_sniffer/MailPoet '.
      '--ignore=./lib/Util/Sudzy/*,./lib/Util/CSS.php,./lib/Util/XLSXWriter.php,'.
      './lib/Config/PopulatorData/Templates/* '.
      'lib/ '.
      $severityFlag
    );
  }

  protected function loadEnv() {
    $dotenv = new Dotenv\Dotenv(__DIR__);
    $dotenv->load();
  }
}
