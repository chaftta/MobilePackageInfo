<?php
require_once 'vendor/autoload.php';

use CFPropertyList\CFPropertyList;
use ApkParser\Parser;

/** パッケージ情報 */
class PackageInfo {
	/** @var string アプリID(ApplicationID or Bundle id) */
	public string $id;
	/** @var string セマンティックバージョニング(versionName or CFBundleShortVersionString) */
	public string $versionName;
	/** @var int バージョン番号(versionCode or CFBundleVersion) */
	public int $versionCode;
	/** コンストラクタ */
	public function __construct(string $id, string $versionName, int $versionCode) {
		$this->id = $id;
		$this->versionName = $versionName;
		$this->versionCode = $versionCode;
	}
	/** デバッグ表示 */
	public function dump(): void {
		echo "ApplicationID: {$this->id}\n";
		echo "VersionName: {$this->versionName}\n";
		echo "VersionCode: {$this->versionCode}\n";
	}
}
/** モバイルプラットフォームのパッケージ情報取得クラス */
class MobilePackageInfo {
	/**
	 * パッケージ情報を取得する
	 * @param string $packagePath パッケージのパス
	 * @return PackageInfo|null パッケージ情報
	 */
	function getPackageInfo(string $packagePath) : PackageInfo | null {
		$extension = strtolower(pathinfo($packagePath, PATHINFO_EXTENSION));
		switch($extension) {
			case 'ipa': return $this->getIpaInfo($packagePath);
			case 'apk': return $this->getApkInfo($packagePath);
		}
		return null;
	}
	/**
	 * IPAのパッケージ情報を取得する
	 * @param string $packagePath IPAのパス
	 * @return PackageInfo|null パッケージ情報
	 */
	function getIpaInfo(string $packagePath) : PackageInfo | null {
		try {
			// Info.plistをデコードする(バイナリのplistの為)
			$contents = $this->loadInfoPlistFromIPA($packagePath);
			if (!$contents) return null;
			$plist = new CFPropertyList();
			$plist->parse($contents);
			$plistData = $plist->toArray();
			return new PackageInfo(
				$plistData['CFBundleIdentifier'],
				$plistData['CFBundleShortVersionString'],
				(int)$plistData['CFBundleVersion']
			);
		} catch (Exception) {
			return null;
		}
	}
	/**
	 * APKのパッケージ情報を取得する
	 * @param string $packagePath APKのパス
	 * @return PackageInfo|null パッケージ情報
	 */
	private function getApkInfo(string $packagePath) : PackageInfo | null {
		try {
			$parser = new Parser($packagePath);
			$manifest = $parser->getManifest();
			return new PackageInfo(
				$manifest->getPackageName(),
				$manifest->getVersionName(),
				$manifest->getVersionCode()
			);
		} catch (Exception) {
			return null;
		}
	}
	/**
	 * IPAファイルからInfo.plistファイルの内容を取得する関数
	 * @param string $ipaFilePath IPAファイルのパス
	 * @return string|false plistの内容
	 */
	function loadInfoPlistFromIPA(string $ipaFilePath): string|false {
		// IPA(ZIP)を開く
		$zip = new ZipArchive();
		if ($zip->open($ipaFilePath) === TRUE) {
			// Payloadフォルダ内のInfo.plistファイルを探す
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$filename = $zip->getNameIndex($i);
				// Payload/*.app/Info.plist パスに一致する場合に処理を行う
				if (preg_match('/^Payload\/[^\/]+\.app\/Info\.plist$/i', $filename)) {
					// Info.plistファイルを開く
					$file = $zip->getStream($filename);
					if ($file) {
						// XMLから連想配列に変換する
						$contents = stream_get_contents($file);
						fclose($file);
						$zip->close();
						return $contents;
					}
				}
			}
			$zip->close();
		}
		return false;
	}
}
