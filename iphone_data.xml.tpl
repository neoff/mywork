<?xml version="1.0" encoding="windows-1251"?>
<itemsList date="{$smarty.now|date_format:"%Y-%m-%d %H:%M"}" type="{$type}">
{if $type=="offers" && $GlobalConfig.cur_action|@count}
<actionInfo>
<name>{$GlobalConfig.cur_action.name}</name>
<dateEnd>{$GlobalConfig.cur_action.end_date}</dateEnd>
<link>http://{$RegionHost}{$GlobalConfig.cur_action.link}</link>
<descr>{$GlobalConfig.cur_action.descr}</descr>
</actionInfo>
{/if}
{if $categories}
<categories>
{foreach from=$categories item=catitem key=catkey}
<category>
<id>{$catkey}</id>
<name>{$catitem.DirName}</name>
<count>{$catitem.count}</count>
<icon>http://www.mvideo.ru/imgs/catalog/dir_{$catkey}.gif</icon>
</category>
{/foreach}
</categories>
{/if}
{foreach from=$itemsList item=item key=key}
<item>
<id>{$item.warecode}</id>
{if $item.DirID}<DirID>{$item.DirID}</DirID>{/if}
<name>{$item.FullName}</name>
<previewText>{if $item.options|@count}{foreach from=$item.options item=opt}{$opt.title};\n{/foreach}{else}{$ware.descr}{/if}</previewText>
<inetPrice>{if $type!="offers" && $RegionID==1 && $item.InetPrice && $item.InetQty && $item.InetPrice<$item.Price}{$item.InetPrice}{/if}</inetPrice>
<price>{$item.Price}</price>
<oldPrice>{if $item.Price<$item.OldPrice}{$item.OldPrice}{/if}</oldPrice>
<priceText>{if $type=="offers" && $GlobalConfig.cur_action|@count && $GlobalConfig.cur_action.priceText && $GlobalConfig.cur_action.priceTextType}{$GlobalConfig.cur_action.priceText}{$item.priceTextVal}{/if}</priceText>
<onlinePrice>{if $RegionID==1 && $item.OnlinePrice}При онлайн-оплате пластиковой картой вы получаете скидку 5% от цены в интернет-магазине.{/if}</onlinePrice>
<currency>RUR</currency>
<previewImageUrl>http://www.mvideo.ru/Pdb/{$item.warecode}s.jpg</previewImageUrl>
<detailImageUrl>http://www.mvideo.ru/Pdb/{$item.warecode}.jpg</detailImageUrl>
<detailText>{if $item.props|@count}{foreach from=$item.props item=property}{$property.PrName}{if $property.PrVal} - {$property.PrVal}{/if};\n{/foreach}\n\n{/if}{$item.ReviewText}</detailText>
<url>http://{$RegionHost}/products/{$item.warecode}.html</url>
</item>
{/foreach}
</itemsList>
