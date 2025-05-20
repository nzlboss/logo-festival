<?php
/**
 * 农历日期计算类
 * 用于阳历和农历日期之间的转换
 */
class LunarCalendar {
    // 农历数据数组
    private $lunarInfo = [
        0x04bd8, 0x04ae0, 0x0a570, 0x054d5, 0x0d260, 0x0d950, 0x16554, 0x056a0, 0x09ad0, 0x055d2,
        0x04ae0, 0x0a5b6, 0x0a4d0, 0x0d250, 0x1d255, 0x0b540, 0x0d6a0, 0x0ada2, 0x095b0, 0x14977,
        0x04970, 0x0a4b0, 0x0b4b5, 0x06a50, 0x06d40, 0x1ab54, 0x02b60, 0x09570, 0x052f2, 0x04970,
        0x06566, 0x0d4a0, 0x0ea50, 0x06e95, 0x05ad0, 0x02b60, 0x186e3, 0x092e0, 0x1c8d7, 0x0c950,
        0x0d4a0, 0x1d8a6, 0x0b550, 0x056a0, 0x1a5b4, 0x025d0, 0x092d0, 0x0d2b2, 0x0a950, 0x0b557,
        0x06ca0, 0x0b550, 0x15355, 0x04da0, 0x0a5d0, 0x14573, 0x052d0, 0x0a9a8, 0x0e950, 0x06aa0,
        0x0aea6, 0x0ab50, 0x04b60, 0x0aae4, 0x0a570, 0x05260, 0x0f263, 0x0d950, 0x05b57, 0x056a0,
        0x096d0, 0x04dd5, 0x04ad0, 0x0a4d0, 0x0d4d4, 0x0d250, 0x0d558, 0x0b540, 0x0b6a0, 0x195a6,
        0x095b0, 0x049b0, 0x0a974, 0x0a4b0, 0x0b27a, 0x06a50, 0x06d40, 0x0af46, 0x0ab60, 0x09570,
        0x04af5, 0x04970, 0x064b0, 0x074a3, 0x0ea50, 0x06b58, 0x055c0, 0x0ab60, 0x096d5, 0x092e0,
        0x0c960, 0x0d954, 0x0d4a0, 0x0da50, 0x07552, 0x056a0, 0x0abb7, 0x025d0, 0x092d0, 0x0cab5,
        0x0a950, 0x0b4a0, 0x0baa4, 0x0ad50, 0x055d9, 0x04ba0, 0x0a5b0, 0x15176, 0x052b0, 0x0a930,
        0x07954, 0x06aa0, 0x0ad50, 0x05b52, 0x04b60, 0x0a6e6, 0x0a4e0, 0x0d260, 0x0ea65, 0x0d530,
        0x05aa0, 0x076a3, 0x096d0, 0x04bd7, 0x04ad0, 0x0a4d0, 0x1d0b6, 0x0d250, 0x0d520, 0x0dd45,
        0x0b5a0, 0x056d0, 0x055b2, 0x049b0, 0x0a577, 0x0a4b0, 0x0aa50, 0x1b255, 0x06d20, 0x0ada0,
    ];

    // 天干地支
    private $tianGan = ['甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸'];
    private $diZhi = ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'];
    private $animals = ['鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪'];

    // 阳历转农历
    public function solarToLunar($year, $month, $day) {
        $offset = (mktime(0, 0, 0, $month, $day, $year) - mktime(0, 0, 0, 1, 31, 1900)) / 86400;
        $yearData = $this->lunarInfo[$year - 1900];
        
        // 计算农历年份
        $y = 1900;
        while ($offset > 0) {
            $days = 348;
            $leapDays = 0;
            
            if ($this->getLeapMonth($y) > 0) {
                $leapDays = $this->getLeapDays($y);
            }
            
            $daysInYear = $days + $leapDays;
            if ($offset >= $daysInYear) {
                $offset -= $daysInYear;
                $y++;
            } else {
                $yearData = $this->lunarInfo[$y - 1900];
                $monthArray = $this->getMonthDays($y);
                $leapMonth = $this->getLeapMonth($y);
                
                // 计算农历月份
                $m = 1;
                while ($m <= 12 && $offset > 0) {
                    $monthDays = 0;
                    if ($m == $leapMonth) {
                        $monthDays = $this->getLeapDays($y);
                        $leapMonth = -1;
                    } else {
                        $monthDays = $monthArray[$m - 1];
                    }
                    
                    $offset -= $monthDays;
                    if ($offset <= 0) break;
                    $m++;
                }
                
                if ($offset < 0) {
                    $offset += $monthDays;
                    $m--;
                }
                
                $lunarMonth = $m;
                $lunarDay = $offset + 1;
                $leap = ($m == $this->getLeapMonth($y)) ? 1 : 0;
                
                return [$lunarMonth, $lunarDay, $leap];
            }
        }
        
        return [0, 0, 0];
    }

    // 农历转阳历
    public function lunarToSolar($year, $month, $day, $isLeap = 0) {
        $offset = 0;
        
        // 计算从1900年1月31日开始的天数偏移
        for ($y = 1900; $y < $year; $y++) {
            $offset += $this->getDaysInYear($y);
        }
        
        $monthArray = $this->getMonthDays($year);
        $leapMonth = $this->getLeapMonth($year);
        
        // 如果是闰月，且不是查询的月份，计算闰月天数
        if ($leapMonth > 0 && $leapMonth == $month && $isLeap) {
            $offset += $this->getLeapDays($year);
        }
        
        // 累加月份天数
        for ($m = 1; $m < $month; $m++) {
            $offset += $monthArray[$m - 1];
            if ($m == $leapMonth) {
                $offset += $this->getLeapDays($year);
            }
        }
        
        // 如果是闰月，加上闰月的天数
        if ($isLeap) {
            $offset += $this->getLeapDays($year);
        }
        
        // 加上当月的天数
        $offset += $day;
        
        // 计算阳历日期
        $solarDate = date('Y-m-d', mktime(0, 0, 0, 1, 31, 1900) + $offset * 86400);
        return $solarDate;
    }

    // 获取某年的总天数
    private function getDaysInYear($year) {
        $days = 348;
        $yearData = $this->lunarInfo[$year - 1900];
        
        for ($i = 0x8000; $i > 0x8; $i >>= 1) {
            $days += ($yearData & $i) ? 1 : 0;
        }
        
        return $days + $this->getLeapDays($year);
    }

    // 获取闰月天数
    private function getLeapDays($year) {
        $yearData = $this->lunarInfo[$year - 1900];
        if ($this->getLeapMonth($year)) {
            return (($yearData & 0x10000) ? 30 : 29);
        }
        return 0;
    }

    // 获取闰月月份
    private function getLeapMonth($year) {
        $yearData = $this->lunarInfo[$year - 1900];
        return ($yearData & 0xF);
    }

    // 获取每月天数
    private function getMonthDays($year) {
        $monthDays = [];
        $yearData = $this->lunarInfo[$year - 1900];
        
        for ($i = 1; $i <= 12; $i++) {
            $monthDays[$i - 1] = (($yearData & (0x10000 >> $i)) ? 30 : 29);
        }
        
        return $monthDays;
    }
}
?>    