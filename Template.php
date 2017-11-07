<?php
/**
 * Created by PhpStorm.
 * User: shooke
 * Date: 17-11-6
 * Time: 下午4:53
 */

class Template
{
    // 模板文件
    public $template = '';
    // 模板路径
    public $templatePath = '/var/www/';
    // 布局文件
    public $layout = 'layout';
    // 编译文件存放路径
    public $compilePath = './';
    // 变量
    public $variable;

    private $lockFile = 'lastupdatetime';


    /**
     * 只渲染模板，主要用于include 标签
     * @param $template
     * @return mixed
     */
    public function renderPartial($template)
    {
        $templateFile = $this->templatePath . $template . '.php';
        $compileFile = $this->compilePath.md5($templateFile).'.php';
        if( !file_exists($compileFile) || filemtime($compileFile) <= filemtime($templateFile) ){
            $compileContent = $this->tagParse(file_get_contents($templateFile));
            // 保存编译结果
            $this->save($compileFile,$compileContent);
        }
        if(is_array($this->variable)){
            extract($this->variable, EXTR_OVERWRITE);
        }
        /* @noinspection PhpIncludeInspection */
        include $compileFile;
    }

    public function render($template,$params=[])
    {
        $this->template = $template;
        $compileFile = $this->getCompileFile();
        if(
            !file_exists($compileFile) ||
            filemtime($compileFile) <= filemtime($this->getTemplateFile())
        ){
            $this->compile($template);
        }
        $this->assign($params);
        if(is_array($this->variable)){
            extract($this->variable, EXTR_OVERWRITE);
        }
        /* @noinspection PhpIncludeInspection */
        include $compileFile;
    }

    /**
     * 清除编译缓存
     */
    public function clean()
    {
        $dh = opendir($this->compilePath);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullPath = $this->compilePath . "/" . $file;
                if (!is_dir($fullPath)) {
                    unlink($fullPath);
                } else {
                    $this->clean($fullPath);
                }
            }
        }
        closedir($dh);
    }
    /**
     * 保存换成文件
     * @param $file
     * @param $content
     */
    public function save($file,$content)
    {
        // 保存文件
        file_put_contents($file,$content);
        // 记录最后更新时间
        file_put_contents($this->lastUpdateFile(),time());
    }

    /**
     * 最后更新时间
     * @return string
     */
    private function lastUpdateFile()
    {
        return $this->compilePath.$this->lockFile;
    }
    /**
     * 编译模板
     */
    public function compile()
    {
        $compileContent = '';//最终编译结果
        $layoutContent = ''; // 布局内容
        $layoutBlock = []; // 布局块
        $templateContent = file_get_contents($this->getTemplateFile());// 模板内容
        $templateBlock = [];// 模板块
        // 处理布局文件
        preg_match('/<' . 'extends\s*([^\/]*)\/{0,1}' . '>/', $templateContent, $match);
        if($match){
            $this->layout = $match[1];
            $templateContent = preg_replace('/<' . 'extends\s*([^\/]*)\/{0,1}' . '>/','',$templateContent);
            $templateContent = preg_replace('/<' . '\/extends' . '>/','',$templateContent);
        }

        //编译布局文件 提取布局块
        if($this->layout){
            $layoutContent = $this->tagParse(file_get_contents($this->getLayoutFile()));
            // 布局块处理
            $layoutBlock = $this->blockParse($layoutContent);

            // 处理继承关系，如果出现<parent name></parent>则调用布局文件中的数据块
            preg_match_all('/<' . 'parent\s+([\s\S]+?)\/{0,1}' .'>/', $templateContent, $extends); // 获取继承关系
            $search = $extends[0];
            $replace = $extends[1];
            foreach ($search as $key=>$val){
                $templateContent = str_replace($val,$layoutBlock[$replace[$key]]['content'],$templateContent);
            }
            $templateContent = preg_replace('/<' . '\/parent' . '>/','',$templateContent); // 删除继承结束标签

            // 模板数据块
            $templateBlock = $this->blockParse($templateContent);

            // 组合数据，将模板数据块覆盖到布局文件
            foreach ($layoutBlock as $key=>$val){
                $layoutContent = str_replace($val['block'],$templateBlock[$key]['content'],$layoutContent);
            }
            // 编译标签
            $compileContent = $this->tagParse($layoutContent);

        }else{
            // 编译标签
            $compileContent = $this->tagParse($templateContent);
        }

        // 保存编译结果
        $this->save($this->getCompileFile(),$compileContent);
    }


    /**
     * 编译文件名称
     * @return string
     */
    public function getCompileFile()
    {
        if(!file_exists($this->compilePath)){
            mkdir($this->compilePath,0777,true);
        }
        return $this->compilePath . md5($this->getTemplateFile()).'.php';
    }

    /**
     * 布局文件名称
     * @return string
     */
    public function getLayoutFile()
    {
        return $this->templatePath . $this->layout.'.php';
    }

    /**
     * 模板文件完整路径名
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->templatePath . $this->template .'.php';
    }
    /**
     * 变量赋值
     * @param $params
     */
    public function assign($params)
    {
        if(is_null($this->variable)){
            $this->variable = $params;
        }else{
            foreach ($params as $key=>$val){
                $this->variable[$key] = $val;
            }
        }
    }
    /**
     * 标签解析
     * @param string $content
     * @return string
     */
    public function tagParse($content)
    {
        $left = '<';
        $right = '>';
        // php标签
        /*
         * <php echo phpinfo();> => <?php echo phpinfo(); ?>
         */
        $template = preg_replace('/' . $left . 'php\s+(.+)' . $right . '/', '<?php \\1?>', $content);

        // if 标签
        /*
         * <if $name==1> => <?php if ($name==1){ ?>
         * <elseif $name==2> => <?php } elseif ($name==2){ ?>
         * <else> 或 <else/> => <?php } else { ?>
         * </if> => <?php } ?>
         */
        $template = preg_replace('/' . $left . 'if\s+(\(.+?\))' . $right . '/', '<?php if \\1 { ?>', $template);
        $template = preg_replace('/' . $left . 'if\s+(.+?)' . $right . '/', '<?php if(\\1) { ?>', $template);
        $template = preg_replace('/' . $left . 'else' . $right . '/', '<?php } else { ?>', $template);
        $template = preg_replace('/' . $left . 'else\/' . $right . '/', '<?php } else { ?>', $template);
        $template = preg_replace('/' . $left . '(elseif|else\s+if)\s+(\(.+?\))' . $right . '/', '<?php } elseif \\2 { ?>', $template);
        $template = preg_replace('/' . $left . '(elseif|else\s+if)\s+(.+?)' . $right . '/', '<?php } elseif (\\2) { ?>', $template);
        $template = preg_replace('/' . $left . '\/if' . $right . '/', '<?php } ?>', $template);

        // for 和 for in 标签
        /**
         * <for ($item,$index) in $array> => <?php foreach($arr as $index=>$item){ ?>
         * <for $item in $array> => <?php foreach($arr as $item){ ?>
         * <for $i=0;$i<10;$i++> => <?php for($i=0;$i<10;$i++) { ?>
         *
         * </for> => <?php } ?>
         */
        $template = preg_replace('/' . $left . 'for\s+\(\s*(.+?)\s*,\s*(.+?)\s*\)\s+in\s+(.+?)' . $right . '/', '<?php foreach(\\3 as \\2 => \\1) { ?>', $template);
        $template = preg_replace('/' . $left . 'for\s+(.+?)\s+in\s+(.+?)' . $right . '/', '<?php foreach(\\2 as \\1) { ?>', $template);
        $template = preg_replace('/' . $left . 'for\s+(\(.+?\))' . $right . '/', '<?php for\\1 { ?>', $template);
        $template = preg_replace('/' . $left . 'for\s+(.+?)' . $right . '/', '<?php for(\\1) { ?>', $template);
        $template = preg_replace('/' . $left . '\/for' . $right . '/', '<?php } ?>', $template);

        // foreach
        $template = preg_replace('/' . $left . 'foreach\s+(\(.+?\))' . $right . '/', '<?php foreach \\1 { ?>', $template);
        $template = preg_replace('/' . $left . '\/foreach' . $right . '/', '<?php } ?>', $template);

        // 常量 函数 变量
        /**
         *  __APP__ => <?php if(defined('__APP__')){echo __APP__;}else{echo '__APP__';} ?>
         * {{ date('Y-m-d H:i:s') }} => <?php echo date('Y-m-d H:i:s');?>
         * {{ $date('Y-m-d H:i:s') }} => <?php echo $date('Y-m-d H:i:s');?>
         * {{ $var }} => <?php echo $var; ?>
         */
        $template = preg_replace('/(__[A-Z]+__)/', '<?php if(defined(\'\\1\')){echo \\1;}else{echo \'\\1\';} ?>', $template);
        $template = preg_replace('/{{\s+(\S+)\s+}}/', '<?php echo \\1; ?>', $template);


        // 载入标签
        /*
         * __APP__ => <?php if(defined('__APP__')){echo __APP__;}else{echo '__APP__';} ?>
         * {{ CONSTANCE }} => <?php echo CONSTANCE;?> 或 {{ CON_STANCE }} => <?php echo CON_STANCE;?>
         * <include head> =>
         * <include head/> =>
         * </include> =>
         */
        $template = preg_replace('/' . $left . 'include\s*([^\/]*)\/{0,1}' . $right . '/i', '<?php echo \$this->renderPartial(\'\\1\'); ?>', $template);
        $template = preg_replace('/' . $left . '\/include' . $right . '/i', '', $template);

        // 注释 编译时清除
        /*
         * <*注释内容*>=>
         * <#注释内容#>=>
         */
        $template = preg_replace('/' . $left . '(\*[\s\S]*\*)' . $right . '/', '', $template);
        $template = preg_replace('/' . $left . '(#[\s\S]*#)' . $right . '/', '', $template);

        return $template;
    }

    /**
     * 布局解析
     * @param string $content
     * @return string
     */
    public function blockParse($content)
    {
        $left = '<';
        $right = '>';
        $layoutStart = $left . 'block\s+(.*?)' . $right; // <block name>
        $layoutEnd = $left . '\/block' . $right; // </block>
        $result = [];

        // 获取内容模板中的内容块
        preg_match_all('/' . $layoutStart . '([\s\S]+?)' . $layoutEnd . '/', $content, $match); // 取得执行前的程序段
        foreach ($match[1] as $key=>$val){
            $result[$val] = [
                'content'=>$match[2][$key],
                'block'=>$match[0][$key]
            ];
        }
        return $result;
    }


}