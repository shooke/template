{{ $title }}
<block title>{{ $title }}</block>
<block content>
    <div>
    我是内容模板，但是我用parent标签，调用了布局文件中content的内容<br>
    <parent content></parent>
    </div>
    <hr>
    <div>
        自身内容
        {{ $content }}
    </div>
    <hr>

    <div>判断
    <if ($title>1)>
        {{ $title }}
    <else>
        没有标题
    </if>
    </div>

    <hr>
    <div>
        <for $item in $array>
            {{ $item }}
        </for>
    </div>

</block>
    <hr>
<block footer>
     <div>
     我是内容页，也载入了footer模板
    <include footer></include>
     </div>
</block>