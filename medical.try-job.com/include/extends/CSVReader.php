<?php
class CSVReader
{
	private $FileName;
	private $ReadHandle;
    private $tempFile;

	function __construct($_FileName)
	{
		$this->FileName		.= $_FileName;
	}

	function readable()
    {
        $this->mb_convert($this->FileName);

		if( !is_file( $this->FileName ) )
			throw new Exception("csvファイルを読み込めません。 [ファイル:".$this->FileName."]");
		if( !$this->ReadHandle ){
			$this->ReadHandle = new SplFileObject($this->FileName);;
			$this->ReadHandle->setFlags(
				SplFileObject::DROP_NEW_LINE | // 行末の改行無視
				SplFileObject::READ_AHEAD | // 先読み
				SplFileObject::SKIP_EMPTY | // 空行無視
				SplFileObject::READ_CSV // CSVとして読み込み
			);
			$this->ReadHandle->setCsvControl(',', '"');
		}
		return ( $this->ReadHandle ? true : false );
	}

    function mb_convert($csvFile){
        global $SYSTEM_CHARACODE;
        $data = file_get_contents($csvFile);
        $fileEncord = mb_detect_encoding($data,"SJIS-win","UTF-8");

        if($fileEncord != $SYSTEM_CHARACODE){
            $data = mb_convert_encoding($data, $SYSTEM_CHARACODE, $fileEncord);

            $this->tempFile = tmpfile();
            fwrite($this->tempFile, $data);
            rewind($this->tempFile);
            $meta = stream_get_meta_data($this->tempFile);

            $this->FileName = $meta['uri'];
        }
    }


    function getReadHandle(){
		return $this->ReadHandle;
	}

	function getLine(){
		return key($this->ReadHandle);
	}

	function __destruct()
    {
        if(!empty($this->tempFile))
            {fclose($this->tempFile);}
    }
}
