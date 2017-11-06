
##模块
以block标签标记
比如<block name></block>

##载入
<include name></include>

##变量或函数调用
{{ $var }}
{{ func() }}

##流程控制
<if $a==1>
	code
	<elseif>
	code
	<else>
	code
</if>
<for $i=1;$i++;$i<5>
	{{ $i }}
</for>

##指定布局文件
<extends layout></extends>