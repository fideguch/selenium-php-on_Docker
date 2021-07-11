<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverBy;

require ('functions.php');

class SampleTest extends TestCase
{
/**
 * @group google
 */
  public function testGetAguReportsInfo()
  {
    // 標準出力時の最初に改行
    echo PHP_EOL;
    // selenium
    $host = 'http://host.docker.internal:4444/wd/hub';
    // chrome ドライバーの起動
    $driver = RemoteWebDriver::create($host,DesiredCapabilities::chrome());

    // ドライバ起動からページ遷移まで
    $driver = getStartPageObj('https://cp.aim.aoyama.ac.jp/lms/lginLgir/', $driver);

    // クラスの配列を受け取る
    $classes = makeClasses($driver);

    // 結果を出力する
    $driver = viewResults($classes, $driver);

    // ブラウザを閉じる
    $driver->close();
  }
}

