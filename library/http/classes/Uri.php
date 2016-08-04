<?php
namespace pillr\library\http;

use \Psr\Http\Message\UriInterface as UriInterface;
/**
 * Value object representing a URI.
 *
 * This interface is meant to represent URIs according to RFC 3986 and to
 * provide methods for most common operations. Additional functionality for
 * working with URIs can be provided on top of the interface or externally.
 * Its primary use is for HTTP requests, but may also be used in other
 * contexts.
 *
 * Instances of this interface are considered immutable{} all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * Typically the Host header will be also be present in the request message.
 * For server-side requests, the scheme will typically be discoverable in the
 * server parameters.
 *
 * @see http://tools.ietf.org/html/rfc3986 (the URI specification)
 */
class Uri implements UriInterface
{

    /**
    * @var string
    */
    private $uri_string, $scheme, $authority, $user_info, $host, $port, $port_string, $path, $query, $fragment;

    /**
    * @var string array
    */
    private $scheme_list = array("http", "https");

    public function __construct($_uri_string){

      if (!is_string($_uri_string)) {
        throw new \InvalidArgumentException($_uri_string);
      }

      if (!filter_var($_uri_string, FILTER_VALIDATE_URL)){
        throw new \InvalidArgumentException('Input must be a valid URL');
      }

      $this->uri_string   = $_uri_string;

      if(parse_url($this->uri_string, PHP_URL_SCHEME) == NULL){
        $this->scheme = '';
      }else{
        $this->scheme = parse_url($this->uri_string, PHP_URL_SCHEME);
      }

      if(parse_url($this->uri_string, PHP_URL_USER) == NULL){
        $this->user_info = '';
      }else{
        $this->user_info = parse_url($this->uri_string, PHP_URL_PHP_URL_USER);

        if(parse_url($this->uri_string, PHP_URL_PASS) != NULL){
          $this->user_info = $this->user_info . ':' . parse_url($this->uri_string, PHP_URL_PASS);
        }
      }

      if(parse_url($this->uri_string, PHP_URL_HOST) == NULL){
        $this->host = '';
      }else{
        $this->host = parse_url($this->uri_string, PHP_URL_HOST);
      }

      if(parse_url($this->uri_string, PHP_URL_PORT) == NULL){
        $this->port_string = '';
      }else{
        $this->port_string = (string)parse_url($this->uri_string, PHP_URL_HOST);
      }

      if((parse_url($this->uri_string, PHP_URL_PORT) != NULL)&&(parse_url($this->uri_string, PHP_URL_PORT) != getservbyname($this->scheme,'tcp'))){
        $this->port = parse_url($this->uri_string, PHP_URL_PORT);
      }else{
        $this->port = NULL;
      }

      $this->makeAuthority();

      if(parse_url($this->uri_string, PHP_URL_PATH) == NULL){
        $this->path = '';
      }else{
        $this->path = parse_url($this->uri_string, PHP_URL_PATH);
      }

      if(parse_url($this->uri_string, PHP_URL_QUERY) == NULL){
        $this->query = '';
      }else{
        $this->query = parse_url($this->uri_string, PHP_URL_QUERY);
      }

      if(parse_url($this->uri_string, PHP_URL_FRAGMENT) == NULL){
        $this->fragment = '';
      }else{
        $this->fragment = parse_url($this->uri_string, PHP_URL_FRAGMENT);
      }

    }

    public function makeAuthority()
    {
      $this->authority = $this->user_info;

      if($this->host != ''){
        if($this->authority != ''){
          $this->authority = $this->authority . '@';
        }
        $this->authority = $this->authority . $this->host;

        if($this->port != NULL){
          $this->authority = $this->authority . ':' . (string)$this->port;
        }
      }
    }

    /**
     * Retrieve the scheme component of the URI.
     *
     * If no scheme is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.1.
     *
     * The trailing ":" character is not part of the scheme and MUST NOT be
     * added.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     * @return string The URI scheme.
     */
    public function getScheme()
    {
      return $this->scheme;
    }

    /**
     * Retrieve the authority component of the URI.
     *
     * If no authority information is present, this method MUST return an empty
     * string.
     *
     * The authority syntax of the URI is:
     *
     * <pre>
     * [user-info@]host[:port]
     * </pre>
     *
     * If the port component is not set or is the standard port for the current
     * scheme, it SHOULD NOT be included.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority()
    {
      return $this->authority;
    }

    /**
     * Retrieve the user information component of the URI.
     *
     * If no user information is present, this method MUST return an empty
     * string.
     *
     * If a user is present in the URI, this will return that value{}
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     *
     * The trailing "@" character is not part of the user information and MUST
     * NOT be added.
     *
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo()
    {
      return $this->user_info;
    }

    /**
     * Retrieve the host component of the URI.
     *
     * If no host is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.2.2.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     * @return string The URI host.
     */
    public function getHost()
    {
      return $this->host;
    }

    /**
     * Retrieve the port component of the URI.
     *
     * If a port is present, and it is non-standard for the current scheme,
     * this method MUST return it as an integer. If the port is the standard port
     * used with the current scheme, this method SHOULD return null.
     *
     * If no port is present, and no scheme is present, this method MUST return
     * a null value.
     *
     * If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null.
     *
     * @return null|int The URI port.
     */
    public function getPort()
    {
      return $this->port;
    }

    /**
     * Retrieve the path component of the URI.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * Normally, the empty path "" and absolute path "/" are considered equal as
     * defined in RFC 7230 Section 2.7.3. But this method MUST NOT automatically
     * do this normalization because in contexts with a trimmed base path, e.g.
     * the front controller, this difference becomes significant. It's the task
     * of the user to handle both "" and "/".
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.3.
     *
     * As an example, if the value should include a slash ("/") not intended as
     * delimiter between path segments, that value MUST be passed in encoded
     * form (e.g., "%2F") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     * @return string The URI path.
     */
    public function getPath()
    {
      return $this->path;
    }

    /**
     * Retrieve the query string of the URI.
     *
     * If no query string is present, this method MUST return an empty string.
     *
     * The leading "?" character is not part of the query and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.4.
     *
     * As an example, if a value in a key/value pair of the query string should
     * include an ampersand ("&") not intended as a delimiter between values,
     * that value MUST be passed in encoded form (e.g., "%26") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.4
     * @return string The URI query string.
     */
    public function getQuery()
    {
      return $this->query;
    }

    /**
     * Retrieve the fragment component of the URI.
     *
     * If no fragment is present, this method MUST return an empty string.
     *
     * The leading "#" character is not part of the fragment and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.5.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.5
     * @return string The URI fragment.
     */
    public function getFragment()
    {
      return $this->fragment;
    }

    /**
     * Return an instance with the specified scheme.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified scheme.
     *
     * Implementations MUST support the schemes "http" and "https" case
     * insensitively, and MAY accommodate other schemes if required.
     *
     * An empty scheme is equivalent to removing the scheme.
     *
     * @param string $scheme The scheme to use with the new instance.
     * @return self A new instance with the specified scheme.
     * @throws \InvalidArgumentException for invalid schemes.
     * @throws \InvalidArgumentException for unsupported schemes.
     */
    public function withScheme($scheme)
    {

      if(!is_string($scheme)){
        throw new \InvalidArgumentException('Input must be a string');
      }

      foreach ($scheme_list as &$schemes) {
          if($schemes == $scheme){
            $this->scheme = $scheme;
            return $this;
          }
      }

      throw new \InvalidArgumentException('Input is not a supported scheme.');

    }

    /**
     * Return an instance with the specified user information.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified user information.
     *
     * Password is optional, but the user information MUST include the
     * user{} an empty string for the user is equivalent to removing user
     * information.
     *
     * @param string $user The user name to use for authority.
     * @param null|string $password The password associated with $user.
     * @return self A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = '')
    {

      if(!is_string($user)){
        throw new \InvalidArgumentException('First argument must be a string');
      }

      if(!is_string($password)){
        throw new \InvalidArgumentException('Input must be a string');
      }

      $this->user = $user;
      $this->password = $password;
      $this->user_info = $this->user;

      if(($this->user != '')&&($this->password != '')){
        $this->user_info = $user . ':' . $password;
      }

      $this->makeAuthority();

      return $this;
    }

    /**
     * Return an instance with the specified host.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified host.
     *
     * An empty host value is equivalent to removing the host.
     *
     * @param string $host The hostname to use with the new instance.
     * @return self A new instance with the specified host.
     * @throws \InvalidArgumentException for invalid hostnames.
     */
    public function withHost($host)
    {
      if(!is_string($host)){
        throw new \InvalidArgumentException('Input must be a string');
      }

      $this->host = $host;
      $this->makeAuthority();
      return $this;
    }

    /**
     * Return an instance with the specified port.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified port.
     *
     * Implementations MUST raise an exception for ports outside the
     * established TCP and UDP port ranges.
     *
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param null|int $port The port to use with the new instance{} a null value
     *     removes the port information.
     * @return self A new instance with the specified port.
     * @throws \InvalidArgumentException for invalid ports.
     */
    public function withPort($port)
    {
      if((!is_int($port))||($port > 1023)&&($port != NULL)){
        throw new \InvalidArgumentException('Input must be a valid port number');
      }

      $this->port = $port;
      if($port == NULL){
        $this->port_string = '';
      }else{
        $this->port_string = (string)$port;
      }
      $this->makeAuthority();
      return $this;
    }

    /**
     * Return an instance with the specified path.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified path.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * If an HTTP path is intended to be host-relative rather than path-relative
     * then it must begin with a slash ("/"). HTTP paths not starting with a slash
     * are assumed to be relative to some base path known to the application or
     * consumer.
     *
     * Users can provide both encoded and decoded path characters.
     * Implementations ensure the correct encoding as outlined in getPath().
     *
     * @param string $path The path to use with the new instance.
     * @return self A new instance with the specified path.
     * @throws \InvalidArgumentException for invalid paths.
     */
    public function withPath($path)
    {
      if(!is_string($path)){
        throw new \InvalidArgumentException('Input must be a string');
      }

      if($path == ''){
        $this->path = '';
      }else if($path[0] != '\\'){
        $this->path = '\\' . $path;
      }else{
        $this->path = $path;
      }

      return $this;
    }

    /**
     * Return an instance with the specified query string.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified query string.
     *
     * Users can provide both encoded and decoded query characters.
     * Implementations ensure the correct encoding as outlined in getQuery().
     *
     * An empty query string value is equivalent to removing the query string.
     *
     * @param string $query The query string to use with the new instance.
     * @return self A new instance with the specified query string.
     * @throws \InvalidArgumentException for invalid query strings.
     */
    public function withQuery($query)
    {
      if(!is_string($query)){
        throw new \InvalidArgumentException('Input must be a string');
      }

      $this->query = $query;
      return $this;
    }

    /**
     * Return an instance with the specified URI fragment.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified URI fragment.
     *
     * Users can provide both encoded and decoded fragment characters.
     * Implementations ensure the correct encoding as outlined in getFragment().
     *
     * An empty fragment value is equivalent to removing the fragment.
     *
     * @param string $fragment The fragment to use with the new instance.
     * @return self A new instance with the specified fragment.
     */
    public function withFragment($fragment)
    {
      if(!is_string($fragment)){
        throw new \InvalidArgumentException('Input must be a string');
      }

      $this->query = $fragment;
      return $this;
    }

    /**
     * Return the string representation as a URI reference.
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     *
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     * @return string
     */
    public function __toString()
    {

      $this->uri_string =  '';

      if($this->scheme != ''){
        $this->uri_string = $this->scheme . '://';
      }

      if($this->authority != ''){
        $this->uri_string = $this->uri_string . $this->authority;
      }

      if($this->path == ''){
        $this->uri_string = $this->uri_string . '\\';
      }else{
        $this->uri_string = $this->uri_string . $this->path;
      }

      if($this->query != ''){
        $this->uri_string = $this->uri_string . '?' . $this->query;
      }

      if($this->fragment != ''){
        $this->uri_string = $this->uri_string . '#' . $this->fragment;
      }

      return $this->uri_string;
    }

}
