<?php
/**
 * 摇号类
 * @author zhjx922
 */
class YaoHao {

    /**
     * 摇号配置
     * @var YaoHaoConfig
     */
    private $_config = array(
        'cycle' =>  0, //期号
        'seed'  =>  0, //6位随机种子数
        'csvSize'  =>  100000, //每个csv的编码数量
        'path'  =>  '', //摇号池编码文件压缩包存放路径
        'numberCsv'  =>  'bjPersonCommonNumberPeriod%d.csv',
        'applyCsv'  =>  'bjPersonCommonApplyNumber%d_%04d.csv',
        'zipFile' => 'PersonCommonNumberPeriod%d.zip'
    );

    /**
     * 摇号总数(非人数，因为有各种阶梯的人。。)
     * @var int
     */
    private $_total;

    /**
     * 摇号指标数
     * @var int
     */
    private $_quota;

    /**
     * 当前随机类
     * @var Random
     */
    private $_random;

    /**
     * 当前出现过的随机数
     * @var array
     */
    private $_randomArray = array();

    /**
     * 中奖Cache
     * @var array
     */
    private $_happyCache = array();

    /**
     * 压缩包实例
     * @var ZipArchive
     */
    private $_zip;

    private $_happyIds = array();

    /**
     * init
     * @param YaoHaoConfig $config
     */
    public function __construct($config)
    {
        $this->_config = array_merge($this->_config, $config);
        $this->_config['zipFile'] = sprintf($this->_config['zipFile'], $this->_config['cycle']);
        $this->_config['numberCsv'] = sprintf($this->_config['numberCsv'], $this->_config['cycle']);
    }

    public function __destruct()
    {
        $this->_zip->close();
    }

    /**
     * 获取zip包文件内容
     * @param $name
     * @param bool $index
     * @param bool $pack
     * @return array|string
     * @throws Exception
     */
    public function getZipFileContent($name, $index = false, $pack = false) {
        $content = $pack ? "" : array();

        if(!$this->_zip) {
            $this->_zip = new ZipArchive;
            if($this->_zip->open($this->_config['path'] . '/' . $this->_config['zipFile']) !== true) {
                throw new Exception('ZIP包打开失败！');
            }
        }

        $handle = $this->_zip->getStream($name);
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if($index !== false) {
                if($pack) {
                    //放到内存总不够用，只能打包了。。
                    $content .= pack('Q', $data[$index]);
                } else {
                    $content[] = $data[$index];
                }
            } else {
                $content[] = $data;
            }
        }

        return $content;
    }

    /**
     * 查询获奖观众编码
     * @param int $randomId
     * @return string
     */
    protected function getHappyId($randomId) {
        $fileNum = (int)floor($randomId / $this->_config['csvSize']) + 1;
        $fileName = sprintf($this->_config['applyCsv'], $this->_config['cycle'], $fileNum);
        $fileLine = $fileNum > 1 ? ($randomId % (($fileNum - 1) * $this->_config['csvSize'])) - 1 : $randomId - 1;

        if(!isset($this->_happyCache[$fileNum])) {
            $this->_happyCache[$fileNum] = $this->getZipFileContent($fileName, 1, true);
        }

        $num = substr($this->_happyCache[$fileNum], $fileLine * 8, 8);

        return sprintf('%013d', unpack('Q', $num)[1]);
    }

    /**
     * 小跑一下
     * @author zhjx922
     */
    protected function run() {
        $count = $this->_total * 10;
        for($i = 0; $i < $count; $i++) {
            $randomId = $this->_random->next($this->_total) + 1;
            if(isset($this->_randomArray[$randomId])) {
                echo "============重复数:{$randomId}==========" . PHP_EOL;
                continue;
            }

            $this->_randomArray[$randomId] = true;

            $happyId = $this->getHappyId($randomId);
            $this->_happyIds[] = $happyId;
            echo "中签编码:{$happyId}\t摇号基数序号:{$randomId}" . PHP_EOL;

            if(count($this->_randomArray) >= $this->_quota) {
                echo "摇号结束！" . PHP_EOL;
                break;
            }
        }
    }

    public function getHappyIds() {
        return $this->_happyIds;
    }

    /**
     * 开始摇号(应该叫抽奖，你懂得)
     */
    public function start() {
        $content = $this->getZipFileContent($this->_config['numberCsv']);

        echo "期号:{$content[1][0]}" . PHP_EOL;
        echo "标题:{$content[2][0]}" . PHP_EOL;
        echo "摇号时间:{$content[3][0]}" . PHP_EOL;
        echo "发布时间:{$content[4][0]}" . PHP_EOL;
        echo "摇号池编码总数:{$content[5][0]}" . PHP_EOL;
        echo "配置指标个数:{$content[6][0]}" . PHP_EOL;
        echo "六位随机种子数:{$this->_config['seed']}" . PHP_EOL;

        //总编码数
        $this->_total = $content[5][0];
        //指标数
        $this->_quota = $content[6][0];

        //根据随机种子初始化随机类
        $this->_random = new Random($this->_config['seed']);

        //开抽
        $this->run();
    }
}