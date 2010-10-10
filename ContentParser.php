<?php 
class ContentParser
{
    const MAIN_RULE = 'main'; 
    
    private $content;
    
    protected $rules = array();
    protected $result = array();
    
    public function __construct($content)
    {
        $this->content = $content;
    }    
    
    public function addRule($name, $regexp, $chunk = self::MAIN_RULE)
    {
        $this->rules[$name] = $regexp;
    }
    
    public function parse()
    {
        foreach($this->rules as $type => $rule) {
            $matches = array();
            preg_match_all($rule, $this->content, $matches);
            $this->result[$type] = $matches[1];
        }
        
        return $this->result;
    } // end parse
    
}
?>