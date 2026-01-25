$(function() {
    //日曆
    $(function() {
        var DATAPICKERAPI = {
            // 
            activeMonthRange: function() {
                return {
                    begin: moment().set({ 'date': 1, 'hour': 0, 'minute': 0, 'second': 0 }).format('YYYY-MM-DD HH:mm:ss'),
                    end: moment().set({ 'hour': 23, 'minute': 59, 'second': 59 }).format('YYYY-MM-DD HH:mm:ss')
                }
            },
            shortcutMonth: function() {
                // 
                var nowDay = moment().get('date');
                var prevMonthFirstDay = moment().subtract(1, 'months').set({ 'date': 1 });
                var prevMonthDay = moment().diff(prevMonthFirstDay, 'days');
                return {
                    now: '-' + nowDay + ',0',
                    prev: '-' + prevMonthDay + ',-' + nowDay
                }
            },
            // 
            rangeMonthShortcutOption1: function() {
                var result = DATAPICKERAPI.shortcutMonth();
                return [{
                    name: '昨天',
                    day: '-1,-1',
                    time: '00:00:00,23:59:59'
                }, {
                    name: '這一月',
                    day: result.now,
                    time: '00:00:00,'
                }, {
                    name: '上一月',
                    day: result.prev,
                    time: '00:00:00,23:59:59'
                }];
            },
            // 
            rangeShortcutOption1: [{
                name: '最近一周',
                day: '-7,0'
            }, {
                name: '最近一個月',
                day: '-30,0'
            }, {
                name: '最近三個月',
                day: '-90, 0'
            }, {
                name: '最近一年',
                day: '-365, 0'
            }],
            singleShortcutOptions1: [{
                name: '今天',
                day: '0'
            }, {
                name: '昨天',
                day: '-1',
                time: '00:00:00'
            }, {
                name: '一周前',
                day: '-7'
            }]
        };
        //
        $('.J-datepicker').datePicker({
            hasShortcut: true,
            min: '2018-01-01 04:00:00',
            max: '2019-04-29 20:59:59',
            shortcutOptions: [{
                name: '今天',
                day: '0'
            }, {
                name: '昨天',
                day: '-1',
                time: '00:00:00'
            }, {
                name: '一周前',
                day: '-7'
            }],
            hide: function() {
                console.info(this)
            }
        });

        //
        $('.J-datepicker-day').datePicker({
            hasShortcut: true,
            format: 'YYYY-MM-DD',
            shortcutOptions: [{
                name: '今天',
                day: '0'
            }, {
                name: '昨天',
                day: '-1'
            }, {
                name: '一周前',
                day: '-7'
            }]
        });

        //
        $('.J-datepicker-range-day').datePicker({
            hasShortcut: true,
            format: 'YYYY-MM-DD',
            isRange: true,
            shortcutOptions: DATAPICKERAPI.rangeShortcutOption1
        });

        //
        $('.J-datepickerTime-single').datePicker({
            format: 'YYYY-MM-DD HH:mm'
        });

        //
        $('.J-datepickerTime-range').datePicker({
            format: 'YYYY-MM-DD HH:mm',
            isRange: true
        });

        //
        $('.J-datepicker-range').datePicker({
            hasShortcut: true,
            min: '2018-01-01 06:00:00',
            max: '2019-04-29 20:59:59',
            isRange: true,
            shortcutOptions: [{
                name: '昨天',
                day: '-1,-1',
                time: '00:00:00,23:59:59'
            }, {
                name: '最近一周',
                day: '-7,0',
                time: '00:00:00,'
            }, {
                name: '最近一個月',
                day: '-30,0',
                time: '00:00:00,'
            }, {
                name: '最近三個月',
                day: '-90, 0',
                time: '00:00:00,'
            }, {
                name: '最近一年',
                day: '-365, 0',
                time: '00:00:00,'
            }]
        });




    });
    ///


});