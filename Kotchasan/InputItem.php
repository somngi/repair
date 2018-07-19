<?php
/**
 * @filesource Kotchasan/InputItem.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * Input Object.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class InputItem
{
    /**
     * ตัวแปรบอกประเภท Input เช่น GET POST SESSION COOKIE.
     *
     * @var string|null
     */
    protected $type;

    /**
     * ตัวแปรเก็บค่าของ Object.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Class Constructer.
     *
     * @param mixed       $value null (default)
     * @param string|null $type  ประเภท Input เช่น GET POST SESSION COOKIE หรือ null ถ้าไม่ได้มาจากรายการข้างต้น
     */
    public function __construct($value = null, $type = null)
    {
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * คืนค่าตามข้อมูลที่ส่งมา.
     *
     * @return mixed
     */
    public function all()
    {
        return $this->value;
    }

    /**
     * ค่าสี.
     *
     * @assert create('#000')->color() [==] '#000'
     * @assert create('red')->color() [==] 'red'
     *
     * @return string
     */
    public function color()
    {
        return $this->filter('\#a-zA-Z0-9');
    }

    /**
     * สร้าง Object.
     *
     * @param mixed       $value
     * @param string|null $type  ประเภท Input เช่น GET POST SESSION COOKIE หรือ null ถ้าไม่ได้มาจากรายการข้างต้น
     *
     * @return \static
     */
    public static function create($value, $type = null)
    {
        return new static($value, $type);
    }

    /**
     * วันที่และเวลา
     * คืนค่า null ถ้าข้อมูลวันที่ว่างเปล่าหรือมีรูปแบบไม่ถูกต้อง.
     *
     * @assert create('2016-01-01 20:20:20')->date() [==] '2016-01-01 20:20:20'
     * @assert create('2016-01-01   20:20:20')->date() [==] '2016-01-01 20:20:20'
     * @assert create('20:20:20')->date() [==] '20:20:20'
     * @assert create('20:20')->date() [==] '20:20:00'
     * @assert create('2016-01-01')->date() [==] '2016-01-01'
     * @assert create('')->date() [==] null
     * @assert create(null)->date() [==] null
     *
     * @param bool $strict true ตรวจสอบความถูกต้องของวันที่ด้วย, false (default) ไม่ต้องตรวจสอบ
     *
     * @return string
     */
    public function date($strict = false)
    {
        $ret = $this->filter('\d\s\-:');
        if ($strict) {
            if (preg_match('/^([0-9]{4,4}\-[0-9]{1,2}\-[0-9]{1,2})?[\s]{0,}([0-9]{1,2}:[0-9]{1,2})?(:[0-9]{1,2})?$/', $ret, $match)) {
                $ret = (empty($match[1]) ? '' : $match[1]).trim(empty($match[2]) ? '' : ' '.$match[2].(empty($match[3]) ? ':00' : $match[3]));
            } else {
                $ret = null;
            }
        }

        return empty($ret) ? null : $ret;
    }

    /**
     * ลบ tag, BBCode ออก ให้เหลือแต่ข้อความล้วน
     * ลบช่องว่างไม่เกิน 1 ช่อง ไม่ขึ้นบรรทัดใหม่
     * และลบช่องว่างหัวท้าย
     * ใช้เป็น description.
     *
     *
     * @assert create("ท.ด(ส     )อ\"บ'\r\n\t<?php echo '555'?>")->description() [==] 'ท.ด(ส )อ บ'
     * @assert create('ทดสอบ<style>body {color: red}</style>')->description() [==] 'ทดสอบ'
     * @assert create('ทดสอบ<b>ตัวหนา</b>')->description() [==] 'ทดสอบตัวหนา'
     * @assert create('ทดสอบ{LNG_Language name}')->description() [==] 'ทดสอบ'
     * @assert create('ทดสอบ[code]ตัวหนา[/code]')->description() [==] 'ทดสอบ'
     * @assert create('ทดสอบ[b]ตัวหนา[/b]')->description() [==] 'ทดสอบตัวหนา'
     * @assert create('2 > 1 < 3 > 2{WIDGET_XXX}')->description() [==] '2 > 1 < 3 > 2'
     * @assert create('ทดสอบ<!--WIDGET_XXX-->')->description() [==] 'ทดสอบ'
     * @assert create('ท&amp;ด&quot;\&nbsp;/__ส-อ+บ')->description() [==] 'ท ด \ /__ส-อ+บ'
     * @assert create('ภาคภูมิ')->description(2) [==] 'ภา'
     *
     * @param int $len ความยาวของ description หมายถึงคืนค่าทั้งหมด
     *
     * @return string
     */
    public function description($len = 0)
    {
        $patt = array(
            /* style */
            '@<(script|style)[^>]*?>.*?</\\1>@isu' => '',
            /* tag */
            '@<[a-z\/\!\?][^>]{0,}>@isu' => '',
            /* keywords */
            '/{(WIDGET|LNG)_[\w\s\.\-\'\(\),%\/:&\#;]+}/su' => '',
            /* BBCode (code) */
            '/(\[code(.+)?\]|\[\/code\]|\[ex(.+)?\])/ui' => '',
            /* BBCode ทั่วไป [b],[i] */
            '/\[([a-z]+)([\s=].*)?\](.*?)\[\/\\1\]/ui' => '\\3',
            /* ตัวอักษรที่ไม่ต้องการ */
            '/(&rdquo;|&quot;|&nbsp;|&amp;|[\r\n\s\t\"\']){1,}/isu' => ' ',
        );
        $text = trim(preg_replace(array_keys($patt), array_values($patt), $this->value));

        return $this->cut($text, $len);
    }

    /**
     * ลบ PHP tag และแปลง \ เป็น $#92; ใช้รับข้อมูลจาก editor
     * เช่นเนื้อหาของบทความ
     *
     * @assert create('ทด\/สอบ<?php echo "555"?>')->detail() [==] 'ทด&#92;/สอบ'
     *
     * @return string
     */
    public function detail()
    {
        return preg_replace(array('/<\?(.*?)\?>/su', '/\\\/'), array('', '&#92;'), $this->value);
    }

    /**
     * ตรวจสอบว่ามีตัวแปรส่งมาหรือไม่
     * คืนค่า true ถ้ามีตัวแปรส่งมา.
     *
     * @return bool
     */
    public function exists()
    {
        return $this->type !== null;
    }

    /**
     * ฟังก์ชั่นแทนที่อักขระที่ไม่ต้องการ.
     *
     * @assert create('admin,1234')->filter('0-9a-zA-Z,') [==] 'admin,1234'
     * @assert create('adminกข,12ฟ34')->filter('0-9a-zA-Z,') [==] 'admin,1234'
     *
     * @param string $format  Regular Expression อักขระที่ยอมรับ เช่น \d\s\-:
     * @param string $replace ข้อความแทนที่
     *
     * @return string
     */
    public function filter($format, $replace = '')
    {
        return trim(preg_replace('/[^'.$format.']/', $replace, $this->value));
    }

    /**
     * ตรวจสอบว่ามาจาก $_COOKIE หรือไม่
     * คืนค่า true ถ้ามาจาก $_COOKIE.
     *
     * @return bool
     */
    public function isCookie()
    {
        return $this->type === 'COOKIE';
    }

    /**
     * ตรวจสอบว่ามาจาก $_GET หรือไม่
     * คืนค่า true ถ้ามาจาก $_GET.
     *
     * @return bool
     */
    public function isGet()
    {
        return $this->type === 'GET';
    }

    /**
     * ตรวจสอบว่ามาจาก $_POST หรือไม่
     * คืนค่า true ถ้ามาจาก $_POST.
     *
     * @return bool
     */
    public function isPost()
    {
        return $this->type === 'POST';
    }

    /**
     * ตรวจสอบว่ามาจาก $_SESSION หรือไม่
     * คืนค่า true ถ้ามาจาก $_SESSION.
     *
     * @return bool
     */
    public function isSession()
    {
        return $this->type === 'SESSION';
    }

    /**
     * ลบ tags และ ลบช่องว่างไม่เกิน 1 ช่อง ไม่ขึ้นบรรทัดใหม่
     * และลบช่องว่างหัวท้าย
     * ใช้เป็น tags หรือ keywords.
     *
     * @assert create("<b>ทด</b>   \r\nสอบ")->keywords() [==] 'ทด สอบ'
     *
     * @param int $len ความยาวของ keywords หมายถึงคืนค่าทั้งหมด
     *
     * @return string
     */
    public function keywords($len = 0)
    {
        $text = trim(preg_replace('/[\r\n\s\t\"\'<>]{1,}/isu', ' ', strip_tags($this->value)));

        return $this->cut($text, $len);
    }

    /**
     * ตัวเลข
     *
     * @assert create(12345)->number() [==] '12345'
     * @assert create(0.12345)->number() [==] '012345'
     * @assert create('ทด0123สอ4บ5')->number() [==] '012345'
     *
     * @return string
     */
    public function number()
    {
        return $this->filter('\d');
    }

    /**
     * รับค่าสำหรับ password อักขระทุกตัวไม่มีช่องว่าง.
     *
     * @assert create(" 0\n12   34\r\r6\t5 ")->password() [==] '0123465'
     *
     * @return string
     */
    public function password()
    {
        return Text::password($this->value);
    }

    /**
     * ฟังก์ชั่นรับข้อความ ยอมรับอักขระทั้งหมด
     * และแปลง ' เป็น &#39;
     * และลบช่องว่างหัวท้าย.
     *
     * @assert create("ทด'สอบ")->quote() [==] "ทด&#39;สอบ"
     *
     * @return string
     */
    public function quote()
    {
        return str_replace("'", '&#39;', trim($this->value));
    }

    /**
     * ฟังก์ชั่น แปลง & " ' < > \ เป็น HTML entities
     * และลบช่องว่างหัวท้าย
     * ใช้แปลงค่าที่รับจาก input ที่ไม่ยอมรับ tag.
     *
     * @assert create(" ทด\/สอบ<?php echo '555'?> ")->text() [==] 'ทด&#92;/สอบ&lt;?php echo &#039;555&#039;?&gt;'
     *
     * @return string
     */
    public function text()
    {
        return trim(Text::htmlspecialchars($this->value));
    }

    /**
     * แปลง < > \ { } เป็น HTML entities และแปลง \n เป็น <br>
     * และลบช่องว่างหัวท้าย
     * ใช้รับข้อมูลที่มาจาก textarea.
     *
     * @assert create("ทด\/สอบ\n<?php echo '555'?>")->textarea() [==] "ทด&#92;/สอบ\n&lt;?php echo '555'?&gt;"
     *
     * @return string
     */
    public function textarea()
    {
        return trim(preg_replace(array('/</s', '/>/s', '/\\\/s', '/\{/', '/\}/'), array('&lt;', '&gt;', '&#92;', '&#x007B;', '&#x007D;'), $this->value));
    }

    /**
     * คืนค่าเป็น boolean.
     *
     * @assert create(true)->toBoolean() [==] 1
     * @assert create(false)->toBoolean() [==] 0
     * @assert create(1)->toBoolean() [==] 1
     * @assert create(0)->toBoolean() [==] 0
     * @assert create(null)->toBoolean() [==] 0
     *
     * @return bool
     */
    public function toBoolean()
    {
        return empty($this->value) ? 0 : 1;
    }

    /**
     * คืนค่าเป็น double.
     *
     * @assert create(0.454)->toDouble() [==] 0.454
     * @assert create(0.545)->toDouble() [==] 0.545
     *
     * @return float
     */
    public function toDouble()
    {
        return (float) $this->value;
    }

    /**
     * คืนค่าเป็น float.
     *
     * @assert create(0.454)->toFloat() [==] 0.454
     * @assert create(0.545)->toFloat() [==] 0.545
     *
     * @return float
     */
    public function toFloat()
    {
        return (float) $this->value;
    }

    /**
     * คืนค่าเป็น integer.
     *
     * @assert create(0.454)->toInt() [==] 0
     * @assert create(2.945)->toInt() [==] 2
     *
     * @return int
     */
    public function toInt()
    {
        return (int) $this->value;
    }

    /**
     * คืนค่าเป็น Object.
     *
     * @assert create('test')->toObject() [==] (object)'test'
     *
     * @return object
     */
    public function toObject()
    {
        return (object) $this->value;
    }

    /**
     * คืนค่าเป็น string หรือ null.
     *
     * @assert create('ทดสอบ')->toString() [==] 'ทดสอบ'
     * @assert create('1')->toString() [==] '1'
     * @assert create(1)->toString() [==] '1'
     * @assert create(null)->toString() [==] null
     *
     * @return string|null
     */
    public function toString()
    {
        return $this->value === null ? null : (string) $this->value;
    }

    /**
     * แปลง tag และ ลบช่องว่างไม่เกิน 1 ช่อง ไม่ขึ้นบรรทัดใหม่
     * เช่นหัวข้อของบทความ
     *
     * @assert create(' ทด\/สอบ'."\r\n\t".'<?php echo \'555\'?> ')->topic() [==] 'ทด&#92;/สอบ &lt;?php echo &#039;555&#039;?&gt;'
     *
     * @return string
     */
    public function topic()
    {
        return Text::topic($this->value);
    }

    /**
     * แปลง tag ไม่แปลง &amp;
     * และลบช่องว่างหัวท้าย
     * สำหรับ URL หรือ email.
     *
     * @assert create(" http://www.kotchasan.com?a=1&b=2&amp;c=3 ")->url() [==] 'http://www.kotchasan.com?a=1&amp;b=2&amp;c=3'
     * @assert create("javascript:alert('xxx')")->url() [==] 'alertxxx'
     * @assert create("http://www.xxx.com/javascript/")->url() [==] 'http://www.xxx.com/javascript/'
     *
     * @return string
     */
    public function url()
    {
        return Text::url($this->value);
    }

    /**
     * รับค่าอีเมลและหมายเลขโทรศัพท์เท่านั้น.
     *
     * @assert create(' admin@demo.com')->username() [==] 'admin@demo.com'
     * @assert create('012 3465')->username() [==] '0123465'
     *
     * @return string
     */
    public function username()
    {
        return Text::username($this->value);
    }

    /**
     * ตัดสตริงค์.
     *
     * @param string $str
     * @param int    $len ความยาวที่ต้องการ
     *
     * @return string
     */
    private function cut($str, $len)
    {
        if (!empty($len) && !empty($str)) {
            $str = mb_substr($str, 0, (int) $len);
        }

        return $str;
    }
}
