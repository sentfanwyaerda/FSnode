#Public Methods Library
**FSnode** contains some additional methods you could use outside the context of FSnode itself.

##FSnode::parse_url()
As an extended version of PHP's [parse_url](http://php.net/manual/en/function.parse-url.php)() it "fixes" two known error situations: 1) *postgres:///dbname* (the triple slash within local unauthenticated connections) and 2) cases where *user* consists of an emailaddress and a single *host* is bound to the *scheme*, like *dropbox://my@server.com:pass/*.