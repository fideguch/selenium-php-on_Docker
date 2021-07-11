<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverBy;

// 調べる項目を記入
define ('CHECKLISTS', ['テスト', 'レポート']);
define ('CHECKSTATUS', ['未実施', '未参照']);

function waitFor ($driver, string $s) {
  $driver->wait(15)->until(
      WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::className($s)));
}

function goBack($driver) {
  //戻る
  $driver->navigate()->back();
  waitFor($driver, "courseCardName");
}

function getEle($driver, string $s, string $do) {
  if ($do === "classNames") {
    $result = $driver->findElements(WebDriverBy::className($s));
  } else if ($do === "className") {
    $result = $driver->findElement(WebDriverBy::className($s));
  } else if ($do === "classNameText") {
    $result = $driver->findElement(WebDriverBy::className($s))->getText();
  } else if ($do === "xpaths") {
    $result = $driver->findElements(WebDriverBy::xpath($s));
  } else if ($do === "xpath") {
    $result = $driver->findElement(WebDriverBy::xpath($s));
  } else if ($do === "xpathText") {
    $result = $driver->findElement(WebDriverBy::xpath($s))->getText();
  } else {
    $result = NULL;
  }
  return $result;
}

function makeClasses ($driver) {
  // 各授業の配列取得
  $classes = getEle($driver, "courseCard", "classNames");
  // 各カードの取得（授業以外は省く）
  foreach ($classes AS $index => $class) {
    $label = getEle($class, '../../div[contains(@class, "cpLabel")]', "xpathText");
    if (strpos($label, 'その他') !== false) unset($classes[$index]);
  }
  $classes = array_values($classes);
  return $classes;
}

function getStartPageObj (string $url, $driver) {
  // 青学のコースパワーに遷移
  $driver->get($url);
  // ログインボタンクリック
  $driver->findElement(WebDriverBy::name("loginButton"))->click();
  // ページが遷移するまで待機
  $driver->wait(10)->until(
  WebDriverExpectedCondition::titleIs('SSO ログインページ')
  );

  // フォームのオブジェクトを格納
  $wrapForm = $driver->findElement(WebDriverBy::tagName('form'));
  // ログインIDとパスワード入力して送信
  $wrapForm->findElement(WebDriverBy::name('j_username'))->sendKeys(getenv('AOYAMA_ID'));
  $wrapForm->findElement(WebDriverBy::name('j_password'))->sendKeys(getenv('AOYAMA_PASS'));
  // ログイン
  $wrapForm->findElement(WebDriverBy::tagName('button'))->click();

  // ページが遷移するまで待機
  waitFor($driver, "courseCardName");
  return $driver;
}

function viewResults($classes, $driver) {
  for ($i = 0; $i < count($classes); $i++) {
    $titleCells = [];
    $judgeCells = [];

    //遷移
    $classes = makeClasses($driver);
    $classes[$i]->click();

    //授業名出力
    $className = getEle($driver, "//div[@class='courseName']", "xpathText");
    printf(PHP_EOL . "●授業名: %s" . PHP_EOL, $className);

    //要素取得、整形
    $driver->findElement(WebDriverBy::className('allOpen'))->click();
    $titleCells = getEle($driver, "//td[contains(@class, 'kyozaititleCell')]", "xpaths");
    $judgeCells = getEle($driver, "//span[contains(@class, 'materialLabe')]", "xpaths");

    //出力
    foreach ($titleCells AS $int => $title) {
      list($logic, $text) = preg_split('/[\p{Z}\p{Cc}]++/u', trim($title->getText()), -1, PREG_SPLIT_NO_EMPTY);
      $status = trim($judgeCells[$int]->getText());
      if (in_array($logic, CHECKLISTS) and in_array($status, CHECKSTATUS)) {
        echo PHP_EOL;
        printf("項目: %s" . PHP_EOL . "題名: %s" . PHP_EOL . "ステータス: %s" . PHP_EOL
          , $logic
          , $text
          , $status
        );
      }
    }
    echo PHP_EOL;

    //戻る
    goBack($driver);
  }
  return $driver;
}
