/////////// Agenda file for CalendarXP 5.0 ////////////
// This file is totally configurable. You may remove all the comments in this file to shrink the download size.
////////////////////////////////////////////////////////
var gsAction="fDemoSetDate(y,m,d);";	// This is for demo use.

////////////// Add Agendas //////////////////////////////////////////
// Usage -- addEvent(date, message, color, action, imgsrc);
// Notice:
// 1. The format of event date is defined in fHoliday() plug-in. Current format is Y-M-D.
// 2. In the action part you can use any javascript statement.
// 3. Assign <null> to action will result in a line-through effect of that day, while <" "> not.
// 4. imgsrc is the tag string to be shown inside the agenda cell, should usually be an image tag.
/////////////////////////////////////////////////////////////////////
addEvent("2001-6-6", "If you arrive on today, then your departure time will be confined!", "lightsteelblue", gsAction);
addEvent("2001-6-20", "If you depart on today, then your arrival time will be confined!", "lightsteelblue", gsAction);


////////////////////////////////////////////////////////////////////////////////
// Holiday PLUG-IN Function -- will return [message,color,action,imgsrc] like agenda!
////////////////////////////////////////////////////////////////////////////////
function fHoliday(y,m,d) {
  var r=agenda[y+"/"+m+"/"+d]; // check agenda table with designated date format
  if (r) return r;	// if there is a defined agenda, then skip the holiday highlights.

  if (m==12&&d==25)
	r=["Feliz Navidad", "seagreen", gsAction];
  else if (m==2&&d==27)
	r=["Batalla de Tarqui", "skyblue", gsAction];
  else if (m==5&&d==24)
	r=["Batalla de Pichincha", "skyblue", gsAction];
 else if (m==1&&d==1)
	r=["Feliz Anio Nuevo", "seagreen", gsAction];
  else if (m==12&&d==31)
	r=["Feliz Anio", "seagreen", gsAction];
  else if (m==8&&d==10)
	r=["Primer Grito de la Independencia", "skyblue", gsAction];
  else if (m==10&&d==9)
	r=["Independencia de Guayaquil", "skyblue", gsAction];
  else if (m==12&&d==6)
	r=["Fundacion de Quito", "skyblue", gsAction];
   else if (m==11&&d==2)
	r=["Dia de los Difuntos", "skyblue", gsAction];


  return r;
}


//////// Put all your self-defined functions to the following /////////
function popup(url, framename) {
  var w=parent.open(url,framename,"top=200,left=200,width=400,height=200,scrollbars=1,resizable=1");
  if (w&&!framename) w.focus();
}

var _dc, _mc, _yc;
function fDemoPopArrive(dayc,monc,yearc,dc1,dc2,ctr) {
  _dc=dayc; _mc=monc; _yc=yearc;
  dc1.value=fFormatDate(_yc.value,_mc.value,_dc.value);
  var sd=fParseDate(dc2.value);
  var range=(sd+''==[2001,6,20]+'')?[[2001,5,2],[2001,6,10],sd]:[];
  fPopCalendar(ctr,dc1, range, null, yearc);
}
function fDemoPopDepart(dc1,dc2,ctr) {
  var sd=fParseDate(dc1.value);
  var range=(sd+''==[2001,6,6]+'')?[[2001,6,10],[2001,7,20],sd,[2001,6,13]]:[];
  fPopCalendar(ctr,dc2, range);
}
function fDemoSetDate(y,m,d) {
	if (gdCtrl.name=="dc1")	{_dc.value=d; _mc.value=m; _yc.value=y;};
}

