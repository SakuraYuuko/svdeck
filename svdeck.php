<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
/**
 * 影之诗卡组展示-handsome主题
 * @version:1.1
 * @author SakuraYuuko
 * https://github.com/SakuraYuuko/svdeck
 *
 * 1.1版————多语言，调用官方api本地缓存json，各职业和中立，语言分别储存一个json
 */

/**
 * 获取职业
 * @param $deckcode string base64
 * @return int
 */
function get_fa_id($deckcode)
{
    $li = base64_decode($deckcode);
    $li2 = explode(",", $li);
    $fa = array_shift($li2);
    return $fa + 1;
}

/**
 * 透过卡组代码获取卡组的数组
 * @param $deckcode string base64
 * @param $jsonstr array
 * @return array
 */
function get_deck_dict($deckcode, $jsonstr)
{
    $li = base64_decode($deckcode);
    $li2 = explode(",", $li);
    $fa = array_shift($li2);
    $deckc = array();


    foreach ($li2 as $m => $p) {
        $p1 = explode(':', $p);
        foreach ($jsonstr as $k1 => $v1) {
            if ($v1['card_id'] == $p1[1]) {
                $kf = array($v1, $p1[0]);
                array_push($deckc, $kf);
            }
        }
    }
    return $deckc;
}

/**
 * 生成卡组
 * @param $svcardb array
 * @return string html
 */
function svdeck_html($svcardb)
{
    $svcardHtml = '<ul class="sv-decklist" style="display:block;margin:0 auto;">';
    if ($svcardb) {
        foreach ($svcardb as $card) {
            $rarf = '';
            switch ($card[0]['rarity']) {
                case '4':
                    $rarf = 'legend';
                    break;
                case '3':
                    $rarf = 'gold';
                    break;
                case '2':
                    $rarf = 'silver';
                    break;
                default:
                    $rarf = 'bronze';
                    break;
            }
            $info = cardInfo($card);
            $svcardHtml .= <<<EOF
                    <li class="deck-entry">
                    <div class="card-mtpis-text" data-mtpis="{$info}">
                        <img src="https://images.weserv.nl/?url=https://shadowverse-portal.com/image/card/phase2/common/L/L_{$card[0]["card_id"]}.jpg" class="deck-entry-image" style="margin: 0px auto;opacity: 0.9;">
                        <div class="card-info">
                            <p class="card-manacost" id="cardinfo" style="background-color:rgba(0,128,0,0.5);color:#f2f2f2">{$card[0]["cost"]}</p>
                            <p class="card-rarity">
                               <i class="card-rarity-f" style="background-image: url(https://images.weserv.nl/?url=https://shadowverse-portal.com/public/assets/image/common/zh-tw/rarity_{$rarf}.png)"></i>
                           </p>
                            <p class="card-name" id="cardinfo">
                                <span class="card-name-text" style="color:#f2f2f2">{$card[0]["card_name"]}</span>
                            </p>
                            <span class="card-info-count" style="color:#f2f2f2">x{$card[1]}</span>
                        </div>
                    </div>
                    </li>
EOF;
        }
    }
    $svcardHtml .= '</ul>';
    return $svcardHtml;
}


/**
 * 卡牌详细信息(略微详细)
 * @param $card array
 * @param $isToken boolean 承认token可以放进卡组，当然这实际上做不到的（或许有人想放剧情卡组）。默认承认
 * @return string
 */
function cardInfo($card,$isToken = true){
    $info='';
    if ($isToken == false && $card[0]['card_set_id'] == 90000){
        //判断是否为token
        $info .= 'token！！！';
    }else{
        //取得费用信息
        $info .= $card[0]['cost'] . 'pp&ensp;';
        switch ($card[0]['char_type']){
            case '1':        //判断是否为随从
                $info .= 'Follower&ensp;';
                $info .= $card[0]['atk'] . '/' . $card['0']['life'];
                $info .= '<br/>' . $card[0]["skill_disc"] . '<br>----------<br>EVO&ensp;' . $card[0]['evo_atk'] . '/' . $card['0']['evo_life'] . '<br/>' . $card[0]["evo_skill_disc"];
                break;
            case '2':        //不带倒数的护符
            case '3':        //带倒数的护符
                $info .= 'Amulet';
                $info .= '<br/>' . $card[0]["skill_disc"];
                break;
            case '4':
                $info .= 'Spell';
                $info .= '<br/>' . $card[0]["skill_disc"];
                break;
        }
    }
    return $info;
}

function getJSON($url = '')
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36');
    curl_setopt($ch, CURLOPT_REFERER, 'https://shadowverse-portal.com/deckbuilder/create/1?lang=zh-tw');


    $output = curl_exec($ch);

    if (FALSE === $output)
        throw new Exception(curl_error($ch), curl_errno($ch));

    curl_close($ch);
    return $output;
}

function get_card_json_i($filePath)
{
    $output = [];
    $fp = fopen($filePath, 'r');
    if ($fp) {
        $contents = fread($fp, filesize($filePath));
        fclose($fp);
        $output = json_decode($contents, true);
    }
    return $output;
}

/**
 * 更新json
 * @param $fa int 职业
 * @param $filePath string
 * @param $lang string 语言
 * @return array
 * @throws Exception
 */
function update_sv($fa, $filePath, $lang = 'zh-tw')
{
    $url = 'https://shadowverse-portal.com/api/v1/cards?format=json&clan=0,' . $fa . '&lang=' . $lang . '';
    $json = getJSON($url);
    $data = fopen($filePath, "w");
    fwrite($data, $json);
    fclose($data);
    $m = get_card_json_i($filePath);
    $cardList = $m['data']['cards'];
    $data = fopen($filePath, "w");
    fwrite($data, json_encode(['time' => time(), 'data' => $cardList]));
    fclose($data);
    return [];
}

/**
 * 获取对应的数组
 * @param $fa int 职业
 * @param $lang string 语言
 * @param $updateBool boolean 是否定时更新，默认关闭
 * @return array
 */
function get_card_json($fa, $lang = 'zh-tw', $updateBool = false)
{
    $output = [];
    $filePath = __DIR__ . '/assets/cache/svzh-' . $fa . '-' . $lang . '.json';
    if (file_exists($filePath) == false) {
        $output = update_sv($fa, $filePath,$lang);
    }
    $fp = fopen($filePath, 'r');
    if ($fp) {
        $contents = fread($fp, filesize($filePath));
        fclose($fp);
        $data = json_decode($contents, true);
        $output = $data['data'];
        if ($updateBool == true) {
            if (time() - $data['time'] > 60 * 60 * 24 * 15) {
                $output = update_sv($fa, $filePath,$lang);
            } else {
                $output = $data['data'];
            }
        }
    }
    return $output;
}

/**
 * 生成造价
 * @param $svcardb array
 * @return int
 */
function vialscount($svcardb)
{
    $vials = 0;
    if ($svcardb) {
        foreach ($svcardb as $card) {
            $tmp = $card[0]['use_red_ether'];
            $vials += $tmp * $card[1];
        }
    }
    return $vials;
}

/**
 * 判断语言
 * @param $lang string
 * @return string
 */
function sv_lang($lang = 'zh-tw'){
    $tmp = strtolower($lang);
    switch ($tmp){
        case 'en':
        case 'ja':
        case 'fr':
        case 'ko':
            break;
        default :
            $tmp = 'zh-tw';
            break;
    }
    $lang = $tmp;
    return $lang;
}

?>

<?php
$deckcode = $this->fields->cdcode;
$svtext = $this->fields->cdname;
$lang = $this->fields->cdlang;
$sv_buttonName = $this->fields->cdbutton;

$sv_buttonName = $sv_buttonName ? $sv_buttonName : '点击查看卡组';
$fa = get_fa_id($deckcode);
$lang = sv_lang($lang);
$jsonstr = get_card_json($fa,$lang);
$svcardb = get_deck_dict($deckcode, $jsonstr);
$svcardHtml = svdeck_html($svcardb);
$vial = vialscount($svcardb);
$svtext .= "【造价：" . $vial . "】";
?>

<div id="deckbutton">
    <!--
    影之诗卡组展示
    @author SakuraYuuko
    @version:1.1
    -->
    <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal2"><?php echo $sv_buttonName;?></button>
    <!-- Modal -->
    <div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="myModalLabel2"><?php echo $svtext; ?></h4>
                </div>
                <div class="modal-body"
                     style="background-image: url(https://images.weserv.nl/?url=https://shadowverse-portal.com/public/assets/image/deckbuilder/zh-tw/classes/<?php echo $fa; ?>/bg.png);background-size: contain;background-repeat: no-repeat;">
                    <?php echo $svcardHtml; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick='window.open("https://sv.bagoum.com/deckbuilder#<?php echo $this->fields->cdcode?>","_blank")'>去Bagoum查看</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                </div>
            </div>
        </div>
    </div>
</div>



