<?php
namespace Arrounded;

/**
 * Object-oriented wrapper for CURL.
 */
class Curl
{
    /**
     * The internal CURL instance.
     *
     * @type resource
     */
    protected $curl;

    /**
     * Build a new Curl instance.
     *
     * @param string|null $url
     * @param array       $options
     */
    public function __construct($url = null, $options = [])
    {
        $this->curl = curl_init();

        // Set endpoint
        if ($url) {
            $this->set('url', $url);
        }

        // Set options
        if ($options) {
            foreach ($options as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

    /**
     * Set a CURL option.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Close the instance.
     */
    public function close()
    {
        curl_close($this->curl);
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// ATTRIBUTES /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param string       $key
     * @param string|array $value
     *
     * @return self
     */
    protected function set($key, $value)
    {
        $option = constant('CURLOPT_'.strtoupper($key));

        curl_setopt($this->curl, $option, $value);

        return $this;
    }

    /**
     * Get an info on the current instance.
     *
     * @param string $info
     *
     * @return mixed
     */
    public function info($info)
    {
        $option = constant('CURLINFO_'.strtoupper($info));

        return curl_getinfo($this->curl, $option);
    }

    /**
     * Set the body of the request.
     *
     * @param string|array $contents
     *
     * @return self
     */
    public function setBody($contents)
    {
        $this->set('postFields', $contents);

        return $this;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// RESPONSE //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Send and get results.
     *
     * @return mixed
     */
    public function send()
    {
        return curl_exec($this->curl);
    }

    /**
     * Get the contents of the remote URL.
     *
     * @return mixed
     */
    public function getBody()
    {
        $this->set('returnTransfer', 1);

        return $this->send();
    }
}
