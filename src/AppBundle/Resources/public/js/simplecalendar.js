function MBCalendar(m, y, date_start, date_end)
{
    this.m = m;
    this.y = y;
    this.weekDays = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

    this.date_start = new Date(date_start);
    this.date_start.setHours(0);
    this.date_start.setMinutes(0);
    this.date_start.setSeconds(0);
    this.date_start.setMilliseconds(0);

    this.date_end = new Date(date_end);
    this.date_end.setHours(0);
    this.date_end.setMinutes(0);
    this.date_end.setSeconds(0);
    this.date_end.setMilliseconds(0);

    this.sameDay = this.date_start.getTime() == this.date_end.getTime();

    var date_tmp_start = new Date(date_start);
    var date_tmp_end = new Date(date_end);
    if (date_tmp_start.getHours() == 0 && 
        date_tmp_start.getMinutes() == 0 &&
        date_tmp_start.getSeconds() == 0 &&
        date_tmp_end.getTime() - date_tmp_start.getTime() == 86400000) {
        this.sameDay = true;
    }
}
 
MBCalendar.prototype.$ =  function(s) {return document.getElementById(s)};
 
// export as array
MBCalendar.prototype.toArray = function() {
    var d;
    var dates = [];

    for (var i=1;i <= new Date(this.y, this.m, 0).getDate();i++)
    {
        d = new Date(this.y,this.m-1, i);
        if (d.getMonth() == this.m-1)
            dates.push(d);
    }
    return dates;
};

function equalDate(date1,date2) {
    return date1.getTime() == date2.getTime();
}
 
// export as html
MBCalendar.prototype.toHTML = function() {

    var dates = this.toArray();
    var i;

    var ret = '';

    // HEADER
    ret += '<tr>';
    for (i in this.weekDays) {
        ret += '<th class="text-center">' + this.weekDays[i].substr(0,1) + '</th>';
    }

    ret += '</tr><tr>';

    var first = ((dates[0].getDay() - 1 + 7) % 7);

    for (i = 0; i < first; i++) {
        ret += '<td></td>';
    }

    for (i = i; i < dates.length+first; i++) {

        if (this.sameDay && equalDate(dates[i-first],this.date_start)) {
            ret += '<td class="oneday info">';
        } 
        else if (!this.sameDay && equalDate(dates[i-first],this.date_start)) {
            ret += '<td class="firstday info">';
        }
        else if (!this.sameDay && equalDate(dates[i-first],this.date_end)) {
            ret += '<td class="lastday info">';
        }
        else if (!this.sameDay && dates[i-first] > this.date_start && dates[i-first] < this.date_end) {
            ret += '<td class="betweenday info">';
        }
        else {
            ret += '<td>'
        }
        ret += dates[i-first].getDate() + '</td>';

        if ((i+1) % 7 == 0) {
            ret += '</tr><tr>';
        }
    }

    for (i = i; (i%7) != 0; i++) {
        ret += '<td></td>';
    }

    ret += '</tr>'

    return ret;
};
 
$(document).ready(function() {
    var $ = function(s) {return document.getElementById(s)};
    var c;
    var y = $('year').value;
    var m = $('month').value;

    var date_start = $('date_start').value;
    var date_end = $('date_end').value;

    c = new MBCalendar(m, y, date_start, date_end);
    $('calendar').innerHTML = c.toHTML();
});