# MobilePackageInfo

このソフトは、Android(APK)とiOS(IPA)のアプリID及びバージョンを取得するライブラリです。

## サンプルコード

```php
<?php

include_once('mobile_package_info.php');
$m = new MobilePackageInfo();

echo "bin/test.apk\n";
$m->getPackageInfo('bin/test.apk')?->dump();

echo "bin/test.ipa\n";
$m->getPackageInfo('bin/test.ipa')?->dump();

//bin/test.apk
//ApplicationID: com.DefaultCompany.com.unity.template.mobile2D
//VersionName: 1.0.0
//VersionCode: 1
//bin/test.ipa
//ApplicationID: com.DefaultCompany.com.unity.template.mobile2D
//VersionName: 1.0.0
//VersionCode: 1
```