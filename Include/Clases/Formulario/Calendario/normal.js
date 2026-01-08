/////////// JS theme file for CalendarXP 5.0 ////////////
// This file is totally configurable. You may remove all the comments in this file to shrink the download size.
////////////////////////////////////////////////////////

// ---- PopCalendar Specific Options ----
var gsSplit="/";	// separator of date string, AT LEAST one char.
var giDatePos=2;	// date format  0: D-M-Y ; 1: M-D-Y; 2: Y-M-D
var gbDigital=true;	// month format   true: 01-05-2001 ; false: 1-May-2001
var gbShortYear=false;   // year format   true: 2-digits; false: 4-digits
var gbAutoPos=true;	// enable auto-adpative positioning or not
var gbPopDown=true;	// true: pop the calendar below the dateCtrl; false: pop above if gbAutoPos is false.

// ---- Common Options ----
var gMonths=["Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"];
var gWeekDay=["Do","Lu","Ma","Mi","Ju","Vi","Sa"];	// weekday caption from Sunday to Saturday

var gBegin=[1910,1,1];	// static Range begin from [Year,Month,Date]
var gEnd=[2030,12,31];	// static Range end at [Year,Month,Date]
var gsOutOfRange="Sorry, you may not go beyond the designated range!";	// out-of-date-range error message

var gbEuroCal=false;	// show european calendar layout - Sunday goes after Saturday

var giDCStyle=0;	// the style of Date Controls.	0: 3D; 1: flat; 2: text-only;
var gsCalTitle="gMonths[gCurMonth[1]-1]+' '+gCurMonth[0]";	// dynamic statement to be eval-ed as the title when giDCStyle>0.
var gbDCSeq=true;	// (effective only when giDCStyle is 0) true: show month box before year box; false: vice-versa;
var gsYearInBox="i";	// dynamic statement to be eval-ed as the text shown in the year box. e.g. "'A.D.'+i" will show "A.D.2001"
var gsNavPrev="<INPUT type='button' value='&lt;' class='MonthNav' onclick='fPrevMonth();this.blur();'>";	// the caption of the left month navigator
var gsNavNext="<INPUT type='button' value='&gt;' class='MonthNav' onclick='fNextMonth();this.blur();'>";	// the caption of the right month navigator

var gbHideBottom=false;	// true: hide the bottom portion; false: show it with gsBottom.
var gsBottom="<A href='javascript:void(0)' class='Today' onclick='var tmp=gCurMonth;gCurMonth=gToday;if(!NN4)this.blur();if(!fSetDate(gToday[0],gToday[1],gToday[2])){gCurMonth=tmp;alert(\"You may not pick this day!\")}return false;' onmouseover='return true;' title='Today'>Hoy : "+gToday[2]+" "+gMonths[gToday[1]-1]+" "+gToday[0]+"</A>";	// the expression of Today-portion at the bottom.

var giCellWidth=18;	// calendar cell width;
var giCellHeight=14;	// calendar cell height;
var gpicBG=null;	// url of background image
var gsBGRepeat="repeat";// repeat mode of background image [no-repeat,repeat,repeat-x,repeat-y]
var gsCalTable="border=0 cellpadding=2 cellspacing=1";	// properties of the calendar inside <table> tag
var gsPopTable=NN4?"border=1 cellpadding=3 cellspacing=0":"border=0 cellpadding=3 cellspacing=0";	// properties of the outmost container <table> tag

var gcBG="#e5e5e5";	// default background color of the cells. Use "" for transparent!!!
var gcCalBG="#00003E";	// background color of the calendar
var gcFrame="dimgray";	// frame color
var gcSat="darkcyan";	// Saturday color
var gcSun="red";	// Sunday color
var gcWorkday="black";	// Workday color
var gcOtherDay="dimgray";	// the day color of other months
var gcToggle="#FFFF80";	// highlight color of the focused cell

var gsCopyright="";
var giHighlightAgenda=3;	// 0: no highlight; 1: highlight with bold-font only (font-size>=8pt); 2: highlight with agenda color only; 3: highlight with both effects.
var giHighlightToday=1; // 0: no highlight; 1: highlight the cell background-color with gsTodayHS (supported in all browsers) ; 2: highlight the cell border with gsTodayHS (not supported in NN4);
var gsTodayHS="white";	// the highlight style of the cell with today's date, it is a string of color or border-style depending on giHighlightToday

var giWeekCell=-1;	// -1: don't show up week counters;  0~7: show week counters at the designated column.
var gsWeekHead="wk";	// the text shown in the table head of week column.
var gsWeeks="w";	// the dynamic statement to be eval-ed into the week counters cell. e.g. "'week '+w" will show "week 1" for the first week of a year.

var gsImg=null;		// default tag string to be used for every non-agenda day, usually an image tag.
var gbHidePadding=false;	// hide the days of non-current months
var gbCrossPast=false;	// line-through all the past dates
var gbMarkSelected=true;	// mark the selected date or not.
