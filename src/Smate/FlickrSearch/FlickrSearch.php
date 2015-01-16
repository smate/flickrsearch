<?php namespace Smate\FlickrSearch;

class FlickrSearch {
    
    protected $apiKey;

    protected $apiSecret; 

    public $text;

    public $perPage = 10;

    private $defaultTimeOut = 10; //timeout for http request in second


    /**
     * constructor
     * @param String apiKey
     */
    public function __construct($apiKey, $apiSecret = false) {
        $this->apiKey = $apiKey;
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
     * @return Array 
     */
    public function search($text)
    {
        $this->text = $text;

        $response = $this->grabResponse($this->buildApiUrl());

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
        return 'https://api.flickr.com/services/rest/?method=flickr.photos.search&api_key='.$this->apiKey.'&text='.$this->text.'&per_page='.$this->perPage.'&format=php_serial';
    }



}
