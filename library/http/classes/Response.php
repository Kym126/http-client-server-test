<?php
namespace pillr\library\http;


use \Psr\Http\Message\ResponseInterface as ResponseInterface;

use \pillr\library\http\Message         as  Message;

/**
 * Representation of an outgoing, server-side response.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - Status code and reason phrase
 * - Headers
 * - Message body
 *
 * Responses are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 */
class Response extends Message implements ResponseInterface
{

    /**
     * @var string
     */
    private $protocol_version, $status_code, $reason_phrase, $message_body;

    /**
     * @var array
     */
    private $headers;

    private $status_codes = array(100, 101, 200, 201, 202, 203,
                                  204, 205, 206, 300, 301, 302,
                                  303, 304, 305, 307, 400, 401,
                                  402, 403, 404, 405, 406, 407,
                                  408, 409, 410, 411, 412, 413,
                                  414, 415, 416, 417, 500, 501,
                                  502, 503, 504, 505);

    private $reason_phrases = array('Continue', 'Switching Protocols', 'OK', 'Created', 'Accepted', 'Non-Authoritative Information',
                                    'No Content', 'Reset Content', 'Partial Content', 'Multiple Choices', 'Moved Permanently', 'Found',
                                    'See Other', 'Not Modified', 'Use Proxy', 'Temporary Redirect', 'Bad Request', 'Unauthorized',
                                    'Payment Required', 'Forbidden', 'Not Found', 'Method Not Allowed', 'Not Acceptable', 'Proxy Authentication Required',
                                    'Request Time-out', 'Conflict', 'Gone', 'Length Required', 'Preondition Failed', 'Request Entity Too Large',
                                    'Request-URI Too Large', 'Unsupported Media Type', 'Requested range not satisfiable', 'Expectation Failed', 'Internal Server Error', 'Not Implemented',
                                    'Bad Gateway', 'Service Unvailable', 'Gateway Time-out', 'HTTP Version not supported');

    public function __construct($_protocol_version, $_status_code, $_reason_phrase, $_headers, $_message_body){

      $this->protocol_version   = $_protocol_version;
      $this->status_code  = $_status_code;
      $this->reason_phrase = $_reason_phrase;
      $this->headers = $_headers;
      $this->message_body = $_message_body;
    }
    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode()
    {
      return (int)$this->status_code;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @see http://tools.ietf.org/html/rfc7231#section-6
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *     provided status code; if none is provided, implementations MAY
     *     use the defaults as suggested in the HTTP specification.
     * @return self
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '')
    {
      var $i = -1;
      foreach ($status_codes as &$codes) {
          $i++;
          if($codes == $code){
            $this->status_code = $code;
            if($reasonPhrase == ''){
              $this->reason_phrase = $reason_phrases[$i];
            }else{
              $this->reason_phrase = $reasonPhrase;
            }

            return $this;
          }
      }

      throw new \InvalidArgumentException('Input should be a valid http status code');
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be empty. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @see http://tools.ietf.org/html/rfc7231#section-6
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase()
    {
      return $this->reason_phrase;
    }
}
