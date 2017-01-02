<?php
/**
* reason backuper
*/
class Reason
{
	protected $creation_date = '';
	protected $dir_name = '';
	protected $targetDir = '';
	protected $dirs = array();
	protected $files = array();
	protected $patterns = array(
		'cmb', // combinator
		'thor', // thor
		'xwv', // malstrom
		'zyp', // subtractor
		'sxt', // nn-xt
		'smp', // nn-19
		'rx2', // rex2
		'drp', // redrum
		'kong', // kong full patch
		'drum', // kong pad patch
		'sm4', // scream unit
		'rv7', // rv-7000
		'grov', // groove
		'wav', // samples
		'rns', // reason song
		'txt', // info
		'jpg', // images or backdrops
		'mid', // mid
	);
	protected $zip = ''; // zip object

	function __construct() {
		$this->creation_date = date('Y-m-d H:i');
		$this->setCurrentDirName();
		$this->targetDir = ".";
		$this->zip = new ZipArchive();
	}

	public function run() {
		$this->writeStatus();
		$this->dirs = $this->rGetDirs($this->targetDir);
		$this->dirs[] = $this->targetDir;    // add current dir to dir list
		$this->files = $this->GetFiles($this->dirs);
		$this->files = $this->filterFiles($this->files, $this->patterns);
//		$groupedFiles = $this->groupFiles($this->files, $this->patterns);
//		$this->writeGroupedData($groupedFiles);
		$this->writeZip($this->files, $this->dir_name . ".zip");
		return true;
	}

	public function setTargetDir($directory) {
		$this->targetDir = $directory;
	}

	protected function setCurrentDirName() {
		$path = getcwd();
		$pieces = explode("\\", $path);
		$this->dir_name = array_pop($pieces);
	}

	protected function writeStatus() {
		$array = array();
		$array['CREATION_DATE'] = $this->creation_date;
		$array['COMPUTERNAME'] = $_SERVER['COMPUTERNAME'];
		$array['USERNAME'] = $_SERVER['USERNAME'];
		$array['FILES'] = $this->patterns;
		$json = json_encode($array);
		$fp = fopen('status.txt', 'w');
		fwrite($fp, $json);
		fclose($fp);
		return true;
	}

	protected function rGetDirs($tDir) {
		$dirs = array();
		$dp = opendir($tDir);
		while (false !== ($element = readdir($dp))) {
			if ($element != "." && $element != "..") {
				if (is_dir($tDir . "\\" . $element)) {
					$dirs[] = $tDir . "\\" .$element;
					if ($x = $this->rGetDirs($tDir . "\\" . $element)) {
						foreach ($x as $value) {
							$dirs[] = $value;
						}
					}
				}
			}
		};
		closedir($dp);
		return $dirs;
	}

	protected function GetFiles($tDir) {
		if (is_string($tDir) or is_array($tDir)) {
			$files = array();
			if (is_array($tDir)) {
				foreach ($tDir as $dir) {
					$dp = opendir($dir);
					while (false !== ($element = readdir($dp))) {
						if (is_file($dir . "\\" . $element)) {
							$files[] = $dir . "\\" . $element;
						}
					}
					closedir($dp);
				}
			} else {
				$dp = opendir($tDir);
				while (false !== ($element = readdir($dp))) {
					if (is_file($tDir . "\\" . $element)) {
						$files[] = $element;
					}
				}
				closedir($dp);
			}
			return $files;
		} else {
			return false;
		}
	}

	protected function groupFiles($files, $patterns) {
		$result = array();
		foreach ($files as $file) {
			foreach ($patterns as $pattern) {
				if (preg_match("/" . $pattern . "$/", $file)) {
					$result[$pattern][] = $file;
				}
			}
		}
		return $result;
	}

	protected function filterFiles($files, $patterns) {
		$result = array();
		foreach ($files as $file) {
			foreach ($patterns as $pattern) {
				if (preg_match("/" . $pattern . "$/", $file)) {
					$result[] = $file;
				}
			}
		}
		return $result;
	}

	protected function writeGroupedData($groupedFiles, $fileName = "data.txt") {
		$fp = fopen($fileName, "w");
		foreach ($groupedFiles as $group => $files) {
			fwrite($fp, "> " . $group . ":\n");
			foreach ($files as $file) {
				fwrite($fp, $file . "\n");
			}
		}
		fclose($fp);
		return true;
	}

	protected function writeZip($files, $fileName = "data.zip") {
		if (is_string($files) or is_array($files)) {
			$this->zip->open($fileName, ZipArchive::CREATE);
			if (is_array($files)) {
				foreach ($files as $file) {
					$this->zip->addFile(ltrim($file, ".\\"));
				}
			} else {
				$this->zip->addFile($files);
			}
			$this->zip->close();
			return true;
		} else {
			return false;
		}
	}

}

$reason = new Reason();
/*if (isset($argv[1])) {
	$reason->setTargetDir($argv[1]);
}*/
$reason->run();