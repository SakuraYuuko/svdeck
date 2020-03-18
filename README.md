# SVDeck
typecho文章加上显示影之诗卡组的功能
适用handsome主题，其他主题请自行调整
# 配置环境
typecho版本 1.1(17.10.30)  
php版本5.6  
handsome主题版本 6.0  
# 使用方法
在`/usr/themes/handsome/`目录里上传这个php文件
修改handsome主题目录下的`post.php`文件，在
```
         <!--文章内容-->
         <div id="post-content" class="wrapper-lg">
          <div class="entry-content l-h-2x">
          <?php echo Content::postContent($this,$this->user->hasLogin());
          ?>
          </div>
```
后面加上
```
             <!--调用sv卡组-->
            <?php if ($this->fields->cdname&&$this->fields->cdcode): ?><?php $this->need('svdeck.php'); ?><?php else: ?>
            <?php endif;?>
```
在`自定义css`那
```CSS
.sv-decklist li{
    list-style: none;
    text-align: -webkit-match-parent;
}
.sv-decklist{
    width: 270px;
    padding-left: 0px;
}
.deck-entry,.deck-entry-image {
    overflow-x: hidden;
    overflow-y: hidden;
    width: 270px;
    max-height: 290px;
}
.deck-entry {
    position: relative;
    visibility: visible;
    width: 270px;
    height: 46px;
    margin-top: 2px;
    cursor: pointer;
    transition: all .35s ease 0s;
    opacity: 1;
    background-position: center;
    -webkit-background-size: 30px 30px;
    background-size: 30px;
}
.card-info {
    top: 0;
    left: 0;
    display: table;
    width: 255px;
    height: 46px;
    position: absolute;
    z-index: 100;
}
.card-name{
    display: table-cell;
    vertical-align: middle;
    margin-bottom: 0px;
    padding-bottom: -20;
    padding-left: 0px;
    padding-right: 0px;
    width: 160px;
    border-left-width: 60px;
    margin-left: 60px;

}
#cardinfo{
    margin-block-start: 1em;
    margin-block-end: 1em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
}
.card-name,.card-name-text{
    font-size: 11px;
    font-weight: 700;
    display: block;
    overflow: hidden;
    padding: 0 5px;
    text-align: left;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.card-info-count{
    font-size: 13px;
    display: table-cell;
    min-width: 37px;
    padding: 0 5px;
    text-align: right;
    vertical-align: middle;
    letter-spacing: 1px;
}
.card-manacost {
    display: table-cell;
    width: 56px;
    padding-top: 14px;
    text-align: center;
    vertical-align: center;
}

#deckbutton {
   text-align:center;
}
.card-rarity>i {
    bottom: 40px;
    right: 0px;
    display: block;
    width: 50px;
    height: 16px;
    position: absolute;
    z-index: auto;
}
#myModal2 .modal-dialog {
    max-width:calc(100% - 20px);
    width:400px
}
#myModal2 .modal-content {
    overflow:hidden
}
```
在`自定义JavaScript`那
这里使用了[这位作者的插件][1]

[1]: link "https://www.jq22.com/jquery-info16219"
```JavaScript

var mTips = {
    c: {
        //配置项
        x: 10, //x偏移量,相对于鼠标
        y: 10, //y偏移量,相对于鼠标

        style: {
            'position': 'fixed',
            'padding': '8px 12px',
            'color': '#fff',
            'border-radius': '5px',
            'font-family': "微软雅黑",
            'z-index': '999',
            'display': 'inline',
            'font-size': '14px',
            'background-color': 'rgba(0, 0, 0, 0.5)',
            'color': '#fff'

        }
    },
    //show方法，用于显示提示

    s: function(text, a, b) {
        var style;
        var fun;

        if(typeof(a) == 'string') {
            style = a;
            fun = b;
        } else if(typeof(a) == 'function') {
            style = b;
            fun = a;
        }

        if(style == 'undefined' || style == null) {
            style = 'default';
        }

        var doc = $('<div></div>').addClass('mTips mTips-' + style).html(text).appendTo('ul.sv-decklist');
        if(doc.css('z-index') !== '999') {
            doc.css(this.c.style);
        }

        $(document).on('mousemove', function(e) {
            $(".mTips").offset({
                top: e.pageY + mTips.c.x,
                left: e.pageX + mTips.c.y
            })
        });

        if(fun != null && typeof(fun) != 'undefined') {
            fun();
        }

    },

    //hide方法，用于隐藏和删除提示
    h: function(fun) {

        $('.mTips').remove();
        if(fun != 'undefined' && fun != null) {
            fun();
        }

    },

    //用于给相关属性添加提示功能
    m: function() {

        $(document).on('mouseenter', '[data-mtpis]', function(e) {
            mTips.s($(this).attr('data-mtpis'), $(this).attr('data-mtpis-style'));
        });

        $(document).on('mouseleave', '[data-mtpis]', function(e) {
            mTips.h();
        });

    }

}
mTips.m(); //通过此函数激活所有的
```

# 自定义字段说明
`cdname`：卡组名称（模态框的标题名）（**必要字段缺一不可**）  
`cdcode`：卡组base64码 （**必要字段缺一不可**）获得方式请在[Bagoum][2]组好牌后，复制地址栏`/deckbuilder#`后面的那串代码，不含井号

[2]: link "https://sv.bagoum.com/deckbuilder"  

`cdlang`：卡牌语言，**如无特殊需求可以不加这自定义字段**，默认繁体中文，其他语言`ja`日语，`en`英语，`fr`法语，`ko`韩语（够用了，我就加了这5种，其他语言需求比如德语、意大利语等请自行修改代码，支持的语言可以从官方组牌器那看）  
`cdbutton`：按钮名称。**如无特殊需求可以不加这自定义字段**，默认`点击查看卡组`  

