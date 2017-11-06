{{ $title }}
<block title>{{ $title }}</block>
<block content>
<div>
布局中的内容
<parent content></parent>
</div>
    <hr>
<div>
    自身内容
    {{ $content }}
</div>
    <hr>
<div>判断
<if $title>1>
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
     内容页
    <include footer></include>
     </div>
</block>