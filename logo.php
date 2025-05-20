<?php
// 设置时区为中国
date_default_timezone_set('Asia/Shanghai');

// 获取当前日期信息
$today = new DateTime();
$year = $today->format('Y');
$month = $today->format('m');
$day = $today->format('d');
$dayOfWeek = $today->format('w'); // 0为周日，1-6为周一到周六

// 包含农历计算类
require_once __DIR__ . '/lunar_calendar.php';

// 验证类是否正确加载
if (!class_exists('LunarCalendar')) {
    die("错误: 找不到农历计算类");
}

$lunar = new LunarCalendar();
// 验证方法是否存在
if (!method_exists($lunar, 'solarToLunar')) {
    die("错误: 农历计算类中缺少 solarToLunar 方法");
}

list($lunarMonth, $lunarDay, $lunarLeap) = $lunar->solarToLunar($year, $month, $day);

// 辅助函数：将农历日期转换为公历日期
function lunarToSolarDate($lunar, $year, $lunarMonth, $lunarDay) {
    $solarDateStr = $lunar->lunarToSolar($year, $lunarMonth, $lunarDay);
    return new DateTime($solarDateStr);
}

// 定义节日及其对应的logo图片
$festivals = [
    // 春节系列 (农历计算)
    'spring_festival_pre' => [
        'name' => '春节预热',
        'logo' => '/image/logo/spring_festival_pre.png',
        'start' => lunarToSolarDate($lunar, $year, 12, 8),  // 腊月初八
        'end' => lunarToSolarDate($lunar, $year, 1, 14),    // 正月十四
    ],
    'spring_festival_main' => [
        'name' => '元宵节',
        'logo' => '/image/logo/spring_festival_main.png',
        'start' => lunarToSolarDate($lunar, $year, 1, 15),  // 正月十五
        'end' => lunarToSolarDate($lunar, $year, 1, 15),    // 正月十五
    ],
    // 端午节 (农历五月初五)
    'dragon_boat_festival' => [
        'name' => '端午节',
        'logo' => '/image/logo/dragon_boat_festival.png',
        'start' => lunarToSolarDate($lunar, $year, 5, 5),
        'end' => lunarToSolarDate($lunar, $year, 5, 5),
    ],
    // 公历节日
    'labor_day' => [
        'name' => '劳动节',
        'logo' => '/image/logo/labor_day.png',
        'start' => new DateTime("$year-05-01"),
        'end' => new DateTime("$year-05-07"),
    ],
    'national_day' => [
        'name' => '国庆节',
        'logo' => '/image/logo/national_day.png',
        'start' => new DateTime("$year-10-01"),
        'end' => new DateTime("$year-10-07"),
    ],
    'christmas' => [
        'name' => '圣诞节',
        'logo' => '/image/logo/christmas.png',
        'start' => new DateTime("$year-12-25"),
        'end' => new DateTime("$year-12-25"),
    ],
];

// 检查跨年的农历节日（如春节可能跨年）
// 如果当前阳历年份的春节还未到，则使用上一年的春节数据
$springFestivalThisYear = lunarToSolarDate($lunar, $year, 1, 1);
if ($today < $springFestivalThisYear) {
    $festivals['spring_festival_pre']['start'] = lunarToSolarDate($lunar, $year-1, 12, 8);
    $festivals['spring_festival_pre']['end'] = lunarToSolarDate($lunar, $year, 1, 14);
    $festivals['spring_festival_main']['start'] = lunarToSolarDate($lunar, $year, 1, 15);
    $festivals['spring_festival_main']['end'] = lunarToSolarDate($lunar, $year, 1, 15);
}

// 默认logo
$defaultLogo = '/image/logo/default.png';
$currentLogo = $defaultLogo;
$currentFestival = '';

// 检查今天是否是某个节日
foreach ($festivals as $key => $festival) {
    if ($today >= $festival['start'] && $today <= $festival['end']) {
        $currentLogo = $festival['logo'];
        $currentFestival = $festival['name'];
        break;
    }
}

// 处理请求
if (isset($_GET['festival'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'festival' => $currentFestival,
        'logo' => $currentLogo,
        'date' => $today->format('Y-m-d'),
        'lunar' => "{$lunarMonth}月{$lunarDay}日" . ($lunarLeap ? ' (闰月)' : '')
    ]);
} else {
    // 如果是直接访问，重定向到当前logo图片
    header("Location: $currentLogo");
}
?>    