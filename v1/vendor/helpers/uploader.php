<?php

# Includes the autoloader for libraries installed with composer
// require __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\Storage\StorageClient;

$privateKeyFileContent = '{
  "type": "service_account",
  "project_id": "social-clark",
  "private_key_id": "ce331b6f2f9d374a3c99a7d81ddaeee069a8d6eb",
  "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDe/8X19sp0q/oe\nAB79wGyfaULIInntit5xo0kUUXRUxTQzxBEQ9WsXtb2So7etPQQtZCKxlwiWSVgK\nzk30B/bfal06F+KOeghWbOHifkUnKkKXM8WU6IT8N3IKPSiXrVsP1vUv0LuCDpJF\ntuY512MwWzE+dQubmYpIfCCdJAAagduN1/JMMQE7BjSH/j5GSDCAqn9za6wKsO4I\nio9Q52/2PQsHueE8vs99hFR/j+TcBxNoMTSDHTJ4movXge5kxyJnKCTzXDRl5a8E\nRp/f8E3JlQl/T257TaPZJdw5Se29cSisWRA7rx9zlHCfS+9iQUr/uWf6B0lfh+BP\nMYINJBC1AgMBAAECggEAAuIKL1MlGuohLgliXS+mN9rVQAKVAPUuSZP/NGqWQssG\n+b6In8SYHpK3SijyPA1OnlYn/KsMStOmdkjwJibIkkfy1shW0GUZTzkXr5mQ206V\npoj1ig9L3TP6TXSs9U7cE51wL4A/sigz4y564U68pHQgon1ZniijGQhwVPTXSJex\n6lYrIK0rMG9wDBldcyM6XdGyFd3rDpSWw9KefeHzh2JTpjRgOGQuY1CjdF0MBijf\nmtef9XMmEqjrOe3q5gOJSR9+4MKWm6QgR8mCX48DCAFIykCZMBGkCaNdvGv/KM3b\nel7KKvKkyIVyUurnc39k3whJmGi5GQOWE5u631zcoQKBgQD7KBzk3UkTJ92hNywQ\n8uf+1q+fhbrLrYpa1hns2bThZ/cV7EnwYIAgd5L5hn2PMmALQ72tgorXcfR6ISSz\nKioNq39W43FBloS0/aToI5D5uu3YbvXnzxknzoiy8VSm/gZLLF0k9rHjk6b8WNiu\nxIAS/PxZrAGw6Rd/ULT4wsb8gwKBgQDjTKeaMptj6+jsj64cXQ80NEoCx6ykVVjR\nswK5KtAgD7C2+rAkYLz3B/6xI4zVdoLKXupw0CscZvsWyOqa8pJON1uszcWQPb4P\nsl57pKlCHZwvHcYjXgBDdLW95BxoPHksUZep66NwwV5r0KB2fx63t5Dq733HZvXR\nTl3Kt+AoZwKBgQDQFuURrlIsP9x+BTNKe6dB2mg6OFeTB57ZMgja12npU/VBQbvl\nIF8oB0pL2WVQboFq895E0yLr7CuNwMYid63OE6h2ug0NNYOQXZLzDvzAHGOR7iVp\nwYnrFFDoSdFEZrWaNRCbXpX2o1A4RfiJnJ8snFUMitn/1LA2OQ0oCmFJtwKBgDjS\nDcfXRM8axzRzyoOeo9FaDPJa9gZyq2xogpLFaBfLtFJZorTFVheu8PVZPLiUEqgC\nSDsBXvWPrM8KVNN9NiPFZO5y1iNzu9JytXnVNNapTkTc/Y4Tzp8OSux4XL2V+Hii\nwPYiSM5EGJV9Js/YgVZN1jVUvy2cyHCVVY76nlebAoGAFI9ZH+sJKEXICISa9Pzd\nm92O6EljTp1QGifTUrYCuA7/I73V6L5H9iJLUutJsUI0dEZDV3kn6EM0HOSyoEkj\nAvG+VdoRoAh2sOioB0+eLFDql3oh+jSKsOaXcyK3Nq8upBeR+kTrkXIuYowD6ZZc\nB6gm9gyD5pmwPHvddmLUUCc=\n-----END PRIVATE KEY-----\n",
  "client_email": "social-clark@appspot.gserviceaccount.com",
  "client_id": "113203684801912647628",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/social-clark%40appspot.gserviceaccount.com"
}';
class Uploader {
    /**
     * Allow image type
     */
    private $imageTypes = array('png', 'jpg', 'gif', 'jpeg');
    private $imageSizes = array(200, 920);
    /**
     * Allowed File types
     */
    private $fileTypes = array(
        'doc',
        'xml',
        'exe',
        'txt',
        'zip',
        'rar',
        'doc',
        'mp3',
        'jpg',
        'png',
        'css',
        'psd',
        'pdf',
        '3gp',
        'ppt',
        'pptx',
        'xls',
        'xlsx',
        'html',
        'docx',
        'fla',
        'avi',
        'mp4',
        'mov',
        'swf',
        'ico',
        'gif',
        'webm',
        'jpeg',
        'wav',
        'csv'
    );

    /**
     * Allowed video types
     */
    private $videoTypes = array('mp4');
    private $audioTypes = array('mp3');
    private $sourceFile;
    private $linkContent = '';
    public $source;
    public $sourceName;
    public $sourceSize;
    public $extension;
    public $destinationPath;
    public $destinationName;
    public $baseDir;

    private $dbType;
    private $dbTypeId;
    private $type;

    //max sizes
    private $maxFileSize = 10000000;
    private $maxImageSize = 10000000;
    private $maxVideoSize = 10000000;
    private $maxAudioSize = 10000000;

    //allow Animated gif
    private $animatedGif = true;

    private $error = false;
    private $errorMessage;
    public $result;
    public $original;
    public  $insertedId;
    public $allowCDN = true;
    /**
     * @param $source
     * @param string $type
     * @param mixed $validate
     */
    public function __construct($source, $type = "image", $validate = false, $fromFile = false, $isLink = false,$itContent = false ,$fileTypes = null)
    {
        $this->source = $source;
        $this->type = $type;
        $this->maxFileSize = model('user')->getAllowSize() * 10000000;
        $this->maxVideoSize = model('user')->getAllowSize() * 10000000;
        $audioSize = config('audio-file-size', 55) * 10000000;
        $this->maxAudioSize = model('user')->getAllowSize() * 10000000;
        $imagesSize = config('image-file-size', 2) * 10000000;
        $this->maxImageSize = model('user')->getAllowSize() * 10000000;
        $videoSize = config('video-file-size', 55) * 10000000;
        $this->maxVideoSize = model('user')->getAllowSize() * 10000000;
        $this->animatedGif = config("support-animated-image", $this->animatedGif);
        $this->imageTypes = explode(',', config('image-file-types', 'jpg,png,gif,jpeg'));
        $this->videoTypes = explode(',', config('video-file-types', 'mp4,mov,wmv,3gp,avi,flv,f4v,webm'));
        $this->audioTypes = explode(',', config('audio-file-types', 'mp3,m4a,mp4'));
        //$this->fileTypes = explode(',', config('files-file-types', 'doc,xml,exe,txt,zip,rar,mp3,jpg,png,css,psd,pdf,3gp,ppt,pptx,xls,xlsx,html,docx,fla,avi,mp4,swf,ico,gif,jpeg,webm'));

        if ($fileTypes) {
            $this->fileTypes = $fileTypes;
        }
        if(!$fromFile) {
            if ($source and $this->source['size'] != 0) {
                $this->source = $source;
                $this->sourceFile = $this->source['tmp_name'];
                $this->sourceSize = $this->source['size'];
                $this->sourceName = $this->source['name'];
                $name = pathinfo($this->sourceName);
                if (isset($name['extension'])) $this->extension = strtolower($name['extension']);

                $this->confirmFile();

            } else {
                if (!$validate) {
                    $this->error = true;
                    $this->errorMessage = l("failed-to-upload-file");
                } else {
                    $this->validate($validate);
                }
            }
        } else {
            $this->source = $this->sourceFile = $this->sourceName = $source;
            if (!$itContent) {
                if (!$isLink) {
                    $name = pathinfo($this->sourceName);
                    if (isset($name['extension'])) $this->extension = strtolower($name['extension']);
                } else {

                    $content = file_get_contents($this->source);

                    if (!$content) {
                        $this->error = true;
                        $this->errorMessage = l("failed-to-upload-file");
                    } else {
                        $this->extension = (get_file_extension($this->source)) ? get_file_extension($this->source) : 'png';
                        $this->linkContent = $content;

                    }

                }
            } else {
                $this->linkContent = $source;
                $this->extension = 'png';
            }
        }

        //load our libraries
        if($this->animatedGif) require_once path("app/vendor/gif_exg.php");
        require_once "app/vendor/PHPImageWorkshop/autoload.php";
        //confirm the creation of uploads directory
        if (!is_dir(path('uploads/'))) {
            @mkdir(path('uploads/'), 0777, true);
            $file = @fopen(path('uploads/index.html'), 'x+');
            fclose($file);
        }

    }

    public function setFileTypes($types) {
        $this->fileTypes = $types;
        return $this;
    }

    public function noThumbnails() {
        $this->imageSizes = array(600, 920);
        return $this;
    }

    public function disableCDN() {
        $this->allowCDN = false;
    }

    public function enableCDN() {
        $this->allowCDN = true;
    }

    /**
     * Method to get the image width
     * @return null
     */
    function getWidth()
    {
        list($width, $height) = getimagesize($this->sourceFile);
        return ($width) ? $width : null;
    }

    /**
     * Method to get the image height
     * @return int
     */
    function getHeight()
    {
        list($width, $height) = getimagesize($this->sourceFile);
        return ($height) ? $height : null;
    }

    public function confirmFile()
    {
        switch($this->type) {
            case 'image':
                if (!in_array($this->extension, $this->imageTypes)){
                    $this->errorMessage = l("upload-file-not-valid-image");
                    $this->error = true;
                }
                if ($this->sourceSize > $this->maxImageSize) {
                    $this->errorMessage = l("upload-image-size-error", array('size' => format_bytes($this->maxImageSize)));
                    $this->error = true;
                }
                break;
            case 'video':
                if (!in_array($this->extension, $this->videoTypes)) {
                    $this->errorMessage = l("upload-file-not-valid-video");
                    $this->error = true;
                }
                if ($this->sourceSize > $this->maxVideoSize) {
                    $this->errorMessage = l("upload-video-size-error", array('size' => format_bytes($this->maxVideoSize)));
                    $this->error = true;
                }
                break;
            case 'audio':
                if (!in_array($this->extension, $this->audioTypes)) {
                    $this->errorMessage = l("upload-file-not-valid-audio");
                    $this->error = true;
                }
                if ($this->sourceSize > $this->maxAudioSize) {
                    $this->errorMessage = l("upload-audio-size-error", array('size' => format_bytes($this->maxAudioSize)));
                    $this->error = true;
                }
                break;
            case 'file':
                if (!in_array($this->extension, $this->fileTypes)) {
                    $this->errorMessage = l("upload-file-not-valid-file");
                    $this->error = true;
                }

                if ($this->sourceSize > $this->maxFileSize) {
                    $this->errorMessage = l("upload-file-size-error", array('size' => format_bytes($this->maxFileSize)));
                    $this->error = true;
                }
                break;
        }
    }

    /**
     * Validate upload files for multiple uploads
     * @param array $files
     * @return boolean
     */
    public function validate($files)
    {
        $isError = false;
        foreach($files as $file){
            $pathInfo = pathinfo($file['name']);
            $this->extension = strtolower($pathInfo['extension']);
            $this->sourceSize = $file['size'];
            switch($this->type) {
                case 'image':
                    if (!in_array($this->extension, $this->imageTypes)){
                        $this->errorMessage = l("upload-file-not-valid-image");
                        $this->error = true;
                    }
                    if ($this->sourceSize > $this->maxImageSize) {
                        $this->errorMessage = l("upload-file-size-error", array('size' => format_bytes($this->maxImageSize)));
                        $this->error = true;
                    }
                    break;
                case 'video':
                    if (!in_array($this->extension, $this->videoTypes)) {
                        $this->errorMessage = l("upload-file-not-valid-video");
                        $this->error = true;
                    }
                    if ($this->sourceSize > $this->maxVideoSize) {
                        $this->errorMessage = l("upload-file-size-error", array('size' => format_bytes($this->maxVideoSize)));
                        $this->error = true;
                    }
                    break;
                case 'audio':
                    if (!in_array($this->extension, $this->audioTypes)) {
                        $this->errorMessage = l("upload-file-not-valid-audio");
                        $this->error = true;
                    }
                    if ($this->sourceSize > $this->maxAudioSize) {
                        $this->errorMessage = l("upload-file-size-error", array('size' => format_bytes($this->maxAudioSize)));
                        $this->error = true;
                    }
                    break;
                case 'file':
                    if (!in_array($this->extension, $this->fileTypes)) {
                        $this->errorMessage = l("upload-file-not-valid-file");
                        $this->error = true;
                    }

                    if ($this->sourceSize > $this->maxFileSize) {
                        $this->errorMessage = l("upload-file-size-error", array('size' => format_bytes($this->maxFileSize)));
                        $this->error = true;
                    }
                    break;
            }
        }
    }

    /**
     * Function to confirm file passes
     */
    public function passed()
    {
        return !$this->error;
    }

    /**
     * Function to set destination
     */

    public function setPath($path)
    {
        $this->baseDir = "uploads/".$path;
        $path = path("uploads/").$path;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
            //create the index.html file
            if (!file_exists($path.'index.html')) {
                $file = fopen($path.'index.html', 'x+');
                fclose($file);
            }
        }
        $this->destinationPath = $path;

        return $this;
    }
    
    public function setRemotePath($objectName, $source)
    {
        /**
         * Upload a file.
         *
         * @param string $bucketName the name of your Google Cloud bucket.
         * @param string $objectName the name of the object.
         * @param string $source the path to the file to upload.
         *
         * @return Psr\Http\Message\StreamInterface
         */
        $bucketName = 'social-clark.appspot.com';
        $storage = new StorageClient();
        $file = fopen($source, 'r');
        $bucket = $storage->bucket($bucketName);
        $object = $bucket->upload($file, [
        'name' => $objectName
        ]);
        printf('Uploaded %s to gs://%s/%s' . PHP_EOL, basename($source));
        // printf('Uploaded %s to gs://%s/%s' . PHP_EOL, basename($source), $bucketName, $objectName);

    }

    /**
     *Function to resize image
     * @param int $width
     * @param int $height
     * @param string $fit
     * @param string $any
     * @return $this
     */
    public function resize($width = null, $height = null, $fit = "inside", $any = "down")
    {
        if ($this->error) return false;

        $fileName = md5($this->sourceName.time()).'.'.$this->extension;
        $this->directUpload(); // first upload original

        $fileName = (!$width) ? '_%w_'.$fileName : '_'.$width.'_'.$fileName;

        $this->result = $this->baseDir.$fileName;

        if ($width) {
            $this->finalizeResize($fileName, $width, $height, $fit, $any);
        } else {
            foreach($this->imageSizes as $size) {
                $this->finalizeResize(str_replace('%w', $size, $fileName), $size, $size, $fit, $any);
            }
        }

        return $this;
    }

    /**
     * @param $filename
     * @param $width
     * @param $height
     * @param $fit
     * @param $any
     */
    private function finalizeResize($filename, $width, $height, $fit, $any)
    {
        try {
            if ($this->animatedGif and $this->extension == "gif") {
                $Gif = new \GIF_eXG($this->sourceFile, 1);
                if (!$height) $height = $width;
                $Gif->resize($this->destinationPath.$filename, $width, $height, 1, 0);
                if(extension_loaded('exif')) {
                    $layer = \PHPImageWorkshop\ImageWorkshop::initFromPath($this->sourceFile, true);
                } else {
                    $layer = \PHPImageWorkshop\ImageWorkshop::initFromPath($this->sourceFile);
                }

                if($width == 550) {
                    $layer->resizeInPixel($width, $height, true);
                }
                elseif ($width < 600) {
                    $layer->cropMaximumInPixel(0, 0, "MM");
                    $layer->resizeInPixel($width, $height);
                } else {
                    $layer->resizeToFit($width, $height, true);
                }
                $filename = str_replace($this->extension, 'jpg', $filename);
                $layer->save($this->destinationPath, $filename);
            } else {
                try{
                    if ($this->linkContent) {
                        $layer = \PHPImageWorkshop\ImageWorkshop::initFromString($this->linkContent);
                    } else {
                        if(extension_loaded('exif')) {
                            $layer = \PHPImageWorkshop\ImageWorkshop::initFromPath($this->sourceFile, true);
                        } else {
                            $layer = \PHPImageWorkshop\ImageWorkshop::initFromPath($this->sourceFile);
                        }
                    }
                    if($width == 550) {
                        $layer->resizeInPixel($width, $height, true);
                    }
                    elseif ($width < 600) {
                        $layer->cropMaximumInPixel(0, 0, "MM");
                        $layer->resizeInPixel($width, $height);
                    } else {
                        $layer->resizeToFit($width, $height, true);
                    }
                } catch (\PHPImageWorkshop\Exception\ImageWorkshopException $e) {
                    exit($e->getMessage());
                }

                $layer->save($this->destinationPath, $filename);
            }

        } catch(Exception $e){
           exit($e->getMessage());
            $this->result = '';
        }
    }

    /**
     * Function to crop image
     * @param int $left
     * @param int $top
     * @param int $width
     * @param int $height
     * @return $this
     */
    public function crop($left = 0, $top = 0, $width = '100%', $height = '100%')
    {
        if ($this->error) return false;
        $fileName = md5($this->sourceName.time()).'.'.$this->extension;
        $fileName = '_'.str_replace('%', '', $width).'_'.$fileName;
        $this->result = $this->baseDir.$fileName;

        try{
            $layer = \PHPImageWorkshop\ImageWorkshop::initFromPath($this->sourceFile, true);
            $layer->cropInPixel($width, $height, $left, $top);
            $layer->save($this->destinationPath, $fileName);

        } catch(Exception $e){$this->result = '';}

        return $this;
    }
    /**
     * Function to get result
     * @return string
     */
    public function result()
    {
        return $this->result;
    }

    /**
     * function to get original image
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * Function to upload video
     */
    public function uploadVideo()
    {
        return $this->directUpload();
    }

    /**
     * function to upload file
     */
    public function uploadFile()
    {
        return $this->directUpload();
    }

    protected function directUpload()
    {        
        if ($this->error) return false;
        $fileName = md5($this->sourceName.time()).".".$this->extension;
        $this->result = $this->baseDir.$fileName;
        move_uploaded_file($this->sourceFile, $this->destinationPath.$fileName);
        if($this->type == "image")
        {
            // rename source file to uploaded file to allow further image processing on it
            $this->sourceFile = $this->original = $this->result;
        }
        return $this;
    }

    public function getError()
    {
        return $this->errorMessage;
    }

    public static function isImage($file) {
        $name = (isset($file['name'])) ? $file['name'] : false;
        if (!$name and $file) $name = $file;
        if ($name ) {
            $name = strtolower($name);
            foreach(array('png', 'jpg', 'gif', 'jpeg') as $type) {
                if (preg_match("#\.$type#", $name)) return true;
            }
        }
        return false;
    }
}
// runGround();