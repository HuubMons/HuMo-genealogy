<?php
echo '<div class="help_box">';
echo '
<p class="help_header">图标</p>
<p class="help_text">- 说明: <br>
<span class="help_explanation">在列表或家庭报表的个人名字左边，有一个 <img src="'.CMS_ROOTPATH.'images/reports.gif" alt="Reports"> 图标，把鼠标移到这个图标上，会弹出一个小长方框，长方框里会根据不同的个人先辈、子孙具体的记录显示几个图标，这些图标表示如下：</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_report.gif" alt="Pedigree">&nbsp;<b>先辈页表</b>: 以被查询人为起点，向上辈查询。用数字来表示是：被查询人看作是数字1，其父是2，其母是3。父的数值是其子的2倍，母的数值是其子的2倍再加1。如某人数值是20，那么其父母数值分别为40和41。<br>在弹出的长方形框菜单中，您也可以查看“先辈图示”<br>您可以点击<a href="http://en.wikipedia.org/wiki/Pedigree_chart" target="blank"><b> 这里 </b></a> 和 <a href="http://en.wikipedia.org/wiki/Ahnentafel" target="blank"><b>这里</b></a>了解更多关于“先辈页表”的信息。</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/descendant.gif" alt="Parenteel">&nbsp;<b>后裔表/图</b>: 以被查询人为起点，向下辈查询。用“第一代”表示被查询人，其后代逐辈加一代，如某人是第一代，其儿子、孙子分别为第二代和第三代。也可以看图表式的“后裔图”。</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/outline.gif" alt="Outline report">&nbsp;<b>简要列表</b>: 以被查询人为起点，向下辈查询，显示（包括其配偶）其后代用列表缩进和世代数字方式简要表示。</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_chart.gif" alt="ancestor sheet">&nbsp;<b>先辈报表</b>: 以被查询人为起点，向上辈五代内查询。被查询人在表格的底行，先辈逐次自下向上列出。 </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/fanchart.gif" alt="Fanchart">&nbsp;<b>饼图</b>: 以被查询人为图形中心，向上辈查询，上辈依世代从内环到外环显示。左侧菜单可以调整饼图信息的显示方式。</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/timeline.gif" alt="Timeline chart">&nbsp;<b>年鉴</b>: 看看同一年份，家庭和历史上都发生了哪些大事件。<br> 如需了解更多信息，请点击该页面左上角的“帮助”链接，</span><br><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '
<p class="help_header">右上角功能区</p>
<p class="help_text">- “搜索”框按钮：<br>
<span class="help_explanation">在网站的所有页面，都有这个搜索框。在搜索框里输入人名，再点击“搜索”按钮，可以向族谱数据库提交查询，并列出匹配结果。</span><br><br>
- “更换外观” 的下拉菜单:<br>
<span class="help_explanation">HuMo-gen程序默认提供了几种不同的外观设计，浏览者可以根据个人喜好，选择不同的外观样式，显示不同的页面背景和颜色。</span><br><br>
- A+ A- 字体放大缩小功能：<br>
<span class="help_explanation">点击A+或A-按钮，可以将HuMo-gen程序显示的字体放大或缩小（注意：需浏览器支持）。</span><br><br>
- 橙色RSS订阅图标（需网站管理员启用该功能）：<br>
<span class="help_explanation">如果订阅，可收到某人生日的通知。（在“工具”下拉菜单中的“本月生日”）。</span><br>

</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo 
'<p class="help_header">“首页”菜单</p><br>
<span class="help_explanation">This takes you to the main Persons Index.
Some panels on this page that require explanation:</span><br>
<p class="help_text">- Owner family tree<br>
<span class="help_explanation">Clicking the name of the site owner will open an email form that allows you to send the site owner a short notice. Please enter your name and email address, so you can be answered. If you wish to send the site owner an attachment (such as a photo or a document) you can use this form to ask the site owner for his email. Then you can use any regular email program to send those attachments. (The email address of the site owner is not published on the site to prevent spamming).</span><br><br>
<p class="help_text">- Search fields<br>
<span class="help_explanation">In the search fields you can search by first and/or last name. You can also choose from three options: "contains", "equals" and "starts with". Note: next to the search button there is an option for  &quot;Advanced Search!&quot;</span><br><br>
- More<br>
<span class="help_explanation">The next few lines are obvious: click the link that you want to move to.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo 
'<p class="help_header">“族谱”菜单</p><br>
<p class="help_text">- 族谱索引<br>
<span class="help_explanation">显示族谱的简要统计信息。</span><br><br>
<p class="help_text">- 个人<br>
<span class="help_explanation">用表格形式显示个人信息。
会显示该族谱里的所有个人，按字母顺序排列。每页至多显示150条记录，可翻页查看更多。“简洁视图”不显示配偶，“扩展视图”显示配偶。</span><br><br>
<p class="help_text">- 名字<br>
<span class="help_explanation">默认列出姓名中间那个字。点“全部名字”链接，会列出名字和名字后边同名的计数。</span><br><br>
<p class="help_text">- 地点（需网站管理员启用此功能）<br>
<span class="help_explanation">您可以集中浏览一些重要的地点，如出生地、安葬地点和结婚地点等。列表以字母顺序列出。
</span><br><br>
</p>';
echo '</div>';



echo '<p><div class="help_box">';
echo '<p class="help_header">“工具”菜单</p><br>
<span class="help_explanation">有几个子菜单：</span><br>
<p class="help_text">- Sources: (only displayed if activated by the site owner)<br>
<span class="help_explanation">Here you will find a list of all sources used in the genealogical research.</span><br><br>
<p class="help_text">- 本月生日<br>
<span class="help_explanation">此页面，显示历年同月出生的人员名单，您也可以查看其它月份的。</span><br><br>
<p class="help_text">- 统计<br>
<span class="help_explanation">此页面，显示已录入人员的统计数据，如“最长寿者”等。</span><br><br>
<p class="help_text">- 找亲戚<br>
<span class="help_explanation">此页面，可对您输入的两个姓名进行搜索，且自动判断这两个人的关系，如“血缘关系”或“夫妻关系”。</span><br><br>
<p class="help_text">- 谷歌地图<br>
<span class="help_explanation">可根据数据中的地点，在谷歌地图中标注显示，还可显示出生或安葬地点。如需了解更多的谷歌地图的高级功能，请点击<a href="http://humogen.com/index.php?option=com_wrapper&view=wrapper&Itemid=58" target="_blank">这里</a>。</span><br><br>
<p class="help_text">- 联系<br>
<span class="help_explanation">此页面，可在线填写表格，联系网站管理员。</span><br>
<p class="help_text">- 最近更新<br>
<span class="help_explanation">此页面，按时间先后显示最近更新的个人名单。可以输入部分姓名来筛选，如输入“振”，会列出以“振”字开头的人名。
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">“语言”菜单</p><br>
<span class="help_explanation">点击相应的旗帜，可切换到相应的语言，如“简体中文”。<br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">“图集”菜单</p>
<p class="help_text">注意：需网站管理员启用该功能。<br><br>
<span class="help_explanation">可查看数据库里的所有图片。<br>点击图片可查看大图，或点击图片旁的人名，转到个人页面查看详情。<br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">“登录”菜单</p><br>
<span class="help_explanation">如果网站管理员有为您提供用户名和密码，在此登录，可查看到相应权限的资料。如生日一般不对公众显示。<br>
</p>';
echo '</div>';
?>
