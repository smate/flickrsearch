<?php 
namespace Smate\FlickrSearch;
use Illuminate\Support\ServiceProvider;

class FlickrSearch extends ServiceProvider {

    
    protected $apiKey;

    protected $apiSecret; 

    public $text;

    public $perPage = 10;

    private $defaultTimeOut = 10; //timeout for http request in second

    private $license = false; //limit search to specific photo license

    public function register() {
    }

    /**
     * constructor
     * @param String apiKey
     */
    public function __construct($apiKey, $apiSecret = false) {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    /**
     * sets perPage param
     * @param int perPage
     */
    public function setPerPage($perPage)
    {
        $this->perPage = intval($perPage);
    }

    /**
     * 
     * @param  String text
     * @param  Int license id 
     *   0 - All Rights Reserved
     *   1 - Attribution-NonCommercial-ShareAlike License
     *   2 - Attribution-NonCommercial License
     *   3 - Attribution-NonCommercial-NoDerivs License
     *   4 - Attribution License
     *   5 - Attribution-ShareAlike License
     *   6 - Attribution-NoDerivs License
     *   7 - No known copyright restrictions
     *   8 - United States Government Work
     * @return Array 
     */
    public function search($text, $license = false)
    {
        $this->text = $text;
        $this->license = $license;
         
        $response = $this->grabResponse($this->buildApiUrl());
        return $response;
    }

    /**
     * call flickr rest api
     * @param  String url
     * @return array 
     * @throws Exception
     */
    private function grabResponse($url)
    {
        $ctx = stream_context_create(array('http'=>
            array(
                'timeout' => $this->defaultTimeOut,
            )
        ));

        $rawData = file_get_contents($url, false, $ctx);

        if( $rawData === false) {
            throw new Exception("Error retrieving data", 1);
        }

        try {
            $data = unserialize($rawData);
        } catch (Exception $e) { //mask error
            throw new Exception("Error unserilizing data", 1);
        }

        return $this->processResponse($data);
    }

    /**
     * process data retrieved from flickr API
     * @param  Array
     * @return Array
     */
    private function processResponse($response)
    {
        $processed = array();

        if( !empty($response) && $response['stat'] == 'ok' && !empty($response['photos']['photo']) ) {
            foreach ($response['photos']['photo'] as $item) {
                $processed[] = $item;
            }
        }

        return $processed;
    }


    /**
     * build url for rest api request
     * @return string
     */
    private function buildApiUrl()
    {
        return 'https://api.flickr.com/services/rest/?method=flickr.photos.search&api_key='.$this->apiKey.'&text='.$this->text.'&per_page='.$this->perPage.'&format=php_serial'.($this->license ? ('&license='.intval($license)) : '');
    }


    /**
     * [getImgURL description]
     * @param  [type]
     * @param  [type]
     * @return [type]
     */
    public static function getImgURL($imageData, $size = 's') 
    {
        if (in_array($size, array('s', 'q', 't', 'm', 'n', '-', 'z', 'c', 'b', 'h', 'k', 'o'))) {
            if($size != '-') {
                $size = '_'.$size;                
            } else {
                $size = '';
            }
        } else {
            $size = '_s';            
        }
        return 'https://farm'.$imageData['farm'].'.staticflickr.com/'.$imageData['server'].'/'.$imageData['id'].'_'.$imageData['secret'].''.$size.'.jpg';
    }
}
