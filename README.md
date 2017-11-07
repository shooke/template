## 简介
使用非常简单，功能非常强大
- 指定布局文件extends  当然这不是必须的，你可以用extends指定，也可以通过类的layout属性指定
- 数据块block  这是一个很强大的功能，可以灵活的控制每一个展示块
- 继承机制parent  使用parent继承调用布局中的内容块
- 多文件组合include  利用include可以将多个模板组合到一起，实现多种不同场景下的代码复用
- 灵活的变量函数以及常量使用
- 优雅的标签逻辑控制 你可以充分利用ide软件的代码提示和自动完成功能，而不需要安装特殊插件
- 模板自动监听 当模板更新时，刷新页面，模板引擎会自动进行编译，展示最新内容

注意：模板引擎监听内容模板，但不会监听布局模板的更新。因为那样太过浪费资源了。当你手动更改了布局文件，这表示你所有的页面都需要更新，这时候我们采取更为高效的方式解决：你可以使用clean方法清空缓存。

## 变量、函数、常量

```
    {{ $title }}

    {{ date('Y-m-d') }}

    __APP__ 两边下划线的常量直接使用，会翻译为&lt;?php if(defined('__APP__')){echo __APP__;}else{echo '__APP__';} ?$gt;

    {{ CONST_VAR }} 普通常量跟变量用法一致，需要用{{}}括起来
```
*注意变量两边有空格，这是为了防止和其他代码发生冲突，比如js里的函数或写在一行上的语句。当有其他地方用到{{}}时将空格去掉就可以了*

## 逻辑标签
```
        if判断
        <if （$var>1）>
            $var&gt1
        <elseif ($var==1)>
           $var=1
        <else/&gt
            $var<1
        </if>

        for循环
        <for ($i=0;$i<5;$i++)>
            {{ $i }}
        </for>
        普通当然for循环

        for in
        <for $item in $array>
            {{ $item['title'] }}
        </for>
        相当于foreach($array as $item)

        <for ($item,$index) in $array>
            {{ $index }}=&gt{{ $item['title'] }}
        </for>
        相当于foreach($array as $index=>$item)

        foreach 跟源生php写法对应只是换成了php标签形式
        <foreach ($array as $item)>
            {{ $item['title'] }}
        </foreach>
        <foreach ($array as $index=>$item)>
            {{ $index }}=&gt{{ $item['title'] }}
        </foreach>
```

## 布局标签
```
        指定布局文件为layout.php
        <extends layout>&lt/extends>

        定义一个content展示块
        <block content></block>

        引入footer模板
        <include footer></include>

        调用布局文件中content展示块的内容
        <parent content></parent>

        
```
*在布局文件和内容模板文件中定义相同的代码块即可。内容模板中的代码块会自动覆盖布局文件中的代码块比如
         布局文件中这样定义*