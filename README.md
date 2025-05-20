
# 节日动态LOGO切换系统（PHP实现）

## 一、项目描述
本系统基于PHP实现，根据当前日期自动切换不同节日的LOGO图片，支持农历和公历节日，默认显示普通LOGO。适用于需要根据节日展示不同品牌形象的网站或应用。


## 二、功能特性
1. **多节日支持**：
   - 农历节日：春节（含预热期）、元宵节、端午节
   - 公历节日：劳动节、国庆节、圣诞节
   - 可扩展其他节日

2. **自动日期判断**：
   - 使用农历计算类处理阴历日期转换
   - 支持跨年节日（如春节可能跨公历年份）

3. **接口能力**：
   - 直接访问 `/logo.php` 自动重定向到当前LOGO
   - 通过 `/logo.php?festival` 获取JSON格式的节日信息


## 三、安装与配置

### 1. 目录结构
```
项目根目录
├─ image/
│  └─ logo/          # LOGO图片存放目录
│     ├─ default.png  # 默认LOGO
│     ├─ spring_festival_pre.png  # 春节预热LOGO
│     ├─ spring_festival_main.png  # 元宵节LOGO
│     ├─ labor_day.png        # 劳动节LOGO
│     ├─ dragon_boat_festival.png  # 端午节LOGO
│     ├─ national_day.png     # 国庆节LOGO
│     └─ christmas.png        # 圣诞节LOGO
├─ logo.php               # 主逻辑文件
└─ lunar_calendar.php     # 农历计算类
```

### 2. 文件获取
- **`logo.php`**：主逻辑脚本，处理节日判断和LOGO重定向
- **`lunar_calendar.php`**：农历日期转换类（已包含在代码中）

### 3. 配置节日
打开 `logo.php`，在 `$festivals` 数组中配置节日信息：
```php
$festivals = [
    // 春节系列（农历计算）
    'spring_festival_pre' => [
        'name' => '春节预热',
        'logo' => '/image/logo/spring_festival_pre.png',
        'start' => lunarToSolarDate($lunar, $year, 12, 8),  // 腊月初八（农历）
        'end' => lunarToSolarDate($lunar, $year, 1, 14),    // 正月十四（农历）
    ],
    // 公历节日示例
    'labor_day' => [
        'name' => '劳动节',
        'logo' => '/image/logo/labor_day.png',
        'start' => new DateTime("$year-05-01"),  // 公历日期直接定义
        'end' => new DateTime("$year-05-07"),
    ],
];
```

#### 配置说明：
- **农历节日**：使用 `lunarToSolarDate($lunar, 农历月份, 农历日期)` 转换为公历日期
- **公历节日**：直接使用 `new DateTime("YYYY-MM-DD")` 定义日期范围
- **LOGO路径**：需与 `image/logo/` 目录下的文件名一致


## 四、使用方法

### 1. 直接访问LOGO
- 普通日期：返回 `default.png`
- 节日期间：自动重定向到对应节日LOGO
- 访问地址：`http://你的域名/logo.php`

### 2. 获取节日信息（JSON接口）
- 地址：`http://你的域名/logo.php?festival`
- 返回示例：
```json
{
    "festival": "春节预热",
    "logo": "/image/logo/spring_festival_pre.png",
    "date": "2025-02-01",
    "lunar": "12月8日"
}
```


## 五、测试与调试

### 1. 临时测试特定节日
修改 `logo.php` 中的节日日期范围，强制匹配当前日期：
```php
// 测试国庆节LOGO（临时修改）
'national_day' => [
    'name' => '国庆节',
    'logo' => '/image/logo/national_day.png',
    'start' => new DateTime("2025-05-20"),  // 修改为当前日期
    'end' => new DateTime("2025-05-20"),
],
```

### 2. 添加测试参数（推荐）
在 `logo.php` 中添加测试参数支持（在节日循环前）：
```php
// 测试模式：强制显示指定节日LOGO
if (isset($_GET['test_festival'])) {
    $testFestival = $_GET['test_festival'];
    if (isset($festivals[$testFestival])) {
        $festivals[$testFestival]['start'] = new DateTime('today');
        $festivals[$testFestival]['end'] = new DateTime('today');
    }
}
```
访问：`http://你的域名/logo.php?test_festival=spring_festival_pre`


## 六、注意事项
1. **时区设置**：
   - 确保 `date_default_timezone_set('Asia/Shanghai')` 已正确配置

2. **农历计算限制**：
   - 目前支持1900-2100年的农历计算（由 `lunar_calendar.php` 决定）
   - 如需扩展年份范围，需修改 `lunarInfo` 数组中的数据

3. **文件权限**：
   - 确保 `image/logo/` 目录可读写（如需动态生成图片）


## 七、扩展节日
如需添加新节日，按以下步骤操作：
1. 在 `$festivals` 数组中添加节日配置
2. 准备对应的LOGO图片（尺寸建议：40×160像素）
3. 测试日期范围是否正确

**示例：添加中秋节（农历八月十五）**
```php
'mid_autumn_festival' => [
    'name' => '中秋节',
    'logo' => '/image/logo/mid_autumn_festival.png',
    'start' => lunarToSolarDate($lunar, $year, 8, 15),
    'end' => lunarToSolarDate($lunar, $year, 8, 15),
],
```


## 八、联系方式
如有问题或建议，可通过以下方式联系：
- 邮箱：qeaf@163.com
