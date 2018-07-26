<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 7/25/2018
 * Time: 4:52 PM
 */
namespace app\index\tags;
use think\template\TagLib;
class Pagination extends TagLib
{
    protected $tags = [
        'page'      =>      ['attr' =>  '', 'close' =>  1]
    ];
    public function tagPage($tag)
    {
        $parse = <<<parse
<?php
    echo time();
?>
<span>123321</span>
parse;
        return $parse;
    }
}