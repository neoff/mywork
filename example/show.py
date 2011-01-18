# -*- coding: utf-8 -*-
#!/usr/bin/env python
'''
Created on 14.01.2011

@author: enesterov
'''
import wx
import wx.html
import urllib
import urllib2
import xml.dom.minidom
from wx.lib.art import flagart, img2pyartprov
import  cStringIO
import base64
from PIL import Image
import sys,os


proxies = {'http': 'http://isa-wan:8080/'}
proxy_handler = urllib2.ProxyHandler({'http': 'http://10.95.1.79:3128/'})
proxy_handler = urllib2.ProxyHandler(proxies)

opener = urllib2.build_opener(proxy_handler)
urllib2.install_opener(opener)

class xmlParser():
	toPage = ""
	toPageBack = ""
	def parseRegion(self, dat):
		d = xml.dom.minidom.parseString(dat)
		slides = d.getElementsByTagName("region")
		self.toPage = ""
		for slide in slides:
			link = slide.getElementsByTagName("region_id")[0]
			title = slide.getElementsByTagName("region_name")[0]
			self.toPage += "<a href=%s>%s</a>\n<br />"%(self.getText(link.childNodes), 
																self.getText(title.childNodes))
		return self.toPage
	
	def parseShop(self, dat):
		d = xml.dom.minidom.parseString(dat)
		slides = d.getElementsByTagName("shop")
		self.toPage = ""
		for slide in slides:
			link = slide.getElementsByTagName("shop_id")[0]
			title = slide.getElementsByTagName("shop_name")[0]
			self.toPage += "<a href=%s>%s</a>\n<br />"%(self.getText(link.childNodes), 
																self.getText(title.childNodes))
		return self.toPage

	def getContent(self, dat):
		d = xml.dom.minidom.parseString(dat)
		elems = d.getElementsByTagName("parent_category")
		categories = d.getElementsByTagName("categories")
		if categories:
			categories = d.getElementsByTagName("products")[0]
		else:
			categories = categories[0]
		for elem in elems:
			
			parent = elem.getElementsByTagName("category_id")[0]
			parent_name = elem.getElementsByTagName("category_name")[0]
			
			print self.getText(parent_name.childNodes)
			self.toPageBack = " &larr;<a href=%s>  %s</a> \n" % (
										self.getText(parent.childNodes),
										self.getText(parent_name.childNodes))
		self.toPageBack += "&nbsp&nbsp&nbsp;&rarr;<a href=%s>%s</a>&larr;<br /><br />\n" % (
								categories.attributes["category_id"].value,
								categories.attributes["category_name"].value)
		
		slides = d.getElementsByTagName("category")
		if len(slides) > 0:
			return self.parseCategory(slides)
		else:
			return self.parseProduct(dat)
		
	def getImage(self, url, i):
		tmp = "%s.jpg" % i
		
		try: 
			data = opener.open(url)
			f = open(tmp, "wb")
			stream = cStringIO.StringIO(data.read())
			f.write(stream.read())
			data.close()
			f.close()
		except urllib2.HTTPError, e:
			print "!!!!! - "+url#print e.reason
			tmp = "none.jpg"
		return tmp
	
	def parseProduct(self, dat):
		d = xml.dom.minidom.parseString(dat)
		slides = d.getElementsByTagName("product")
		#slides = d.getElementsByTagName("product")
		self.toPage = ""
		self.toPage += self.toPageBack
		i = 0
		for slide in slides:
			link = slide.getElementsByTagName("product_id")[0]
			title = slide.getElementsByTagName("title")[0]
			ico = slide.getElementsByTagName("image")[0]
			print self.getText(ico.childNodes)
			
			tmp = self.getImage(self.getText(ico.childNodes), i)
			self.toPage += "<a href=%s><img border=0 height=80 width=80 src=\"%s\">%s</a> \n<br />"%(
																self.getText(link.childNodes),
																tmp, 
																self.getText(title.childNodes))
			i+=1
		return self.toPage
	
	
	def parseCategory(self, slides):
		self.toPage = ""
		self.toPage += self.toPageBack
		i = 0
		for slide in slides:
			link = slide.getElementsByTagName("category_id")[0]
			title = slide.getElementsByTagName("category_name")[0]
			ico = slide.getElementsByTagName("category_icon")[0]
			amount = slide.getElementsByTagName("amount")[0]
			print self.getText(ico.childNodes)
			#f = opener.open(self.getText(ico.childNodes)).read()
			#data = opener.open(self.getText(ico.childNodes)).read()
			#stream = cStringIO.StringIO(data)
			#bmp = wx.BitmapFromImage( wx.ImageFromStream( stream ))
			tmp = self.getImage(self.getText(ico.childNodes), i)
			self.toPage += "<a href=%s><img height=90 width=90 border=0 src=\"%s\">%s</a>(%s)\n<br />"%(
																self.getText(link.childNodes),
																tmp, 
																self.getText(title.childNodes),
																self.getText(amount.childNodes))
			i+=1
		return self.toPage
	
	def getText(self, nodelist):
		rc = []
		for node in nodelist:
			if node.nodeType == node.TEXT_NODE:
				rc.append(node.data)
		return ''.join(rc)
	
	


class HtmlWindow(wx.html.HtmlWindow):
	params = {'region_id': 0}
	#Url = "http://www.mvideo.ru/mobile/?%s"
	Url = "http://www-test.corp.mvideo.ru/mobile/?%s"
	xp = xmlParser()
	
		

		
	def OnLinkClicked(self, link):
		links = link.GetHref()
		if self.params['region_id'] == 0:
			self.params['region_id'] = links
			self.params['category_id'] = 0
		else:
			self.params['category_id'] = links
		self.getUrl()
	
	def getUrl(self):
		toPage = self.xp.getContent(self.openUrl())
		self.SetPage(toPage)
		
	def openUrl(self):
		link = urllib.urlencode(self.params)
		#opener = urllib.FancyURLopener(self.proxies)
		#proxy_auth_handler = urllib2.ProxyBasicAuthHandler()
		#proxy_auth_handler.add_password('realm', 'host', 'enesterov', '11223344')
		
		#f = urllib.urlopen(self.Url % link)
		
		f = opener.open(self.Url % link)
		print self.Url % link
		return f.read()

class Form1(wx.Panel):
	def __init__(self, parent):
		#self.log = log
		wx.Panel.__init__(self, parent, -1)

		# Set up some basic element. Placement is a bit crude as this
		# is really an event handling demo! It would also be a good idea
		# to define constants for the IDs!

		


class MyFrame(wx.Frame):
	""" We simply derive a new class of Frame. """
	def __init__(self, parent, title):
		self.parent = parent
		self.frame = wx.Frame.__init__(self, parent, title=title, size=(320,480))
		
		#self.CreateStatusBar()
		#self.st = self.SetStatusText
		#self.st("This is the statusbar")
		
		self.control = HtmlWindow(self)
		
		
		menu = wx.Menu()
		menu.Append(wx.ID_ABOUT, "&About",
					"More information about this program")
		menu.AppendSeparator()
		menu.Append(wx.ID_EXIT, "E&xit", "Terminate the program")
		menuBar = wx.MenuBar()
		menuBar.Append(menu, "&File");
		self.SetMenuBar(menuBar)

		wx.EVT_MENU(self, wx.ID_ABOUT, self.OnAbout)
		wx.EVT_MENU(self, wx.ID_EXIT,  self.TimeToQuit)
		
		
		
		
		#grid = wx.HTMLWindow(self)
		self.toPage = ""
		
		self.Show(True)
		self.xp = xmlParser()
		#proxies = {'http': 'http://isa-wan:8080/'}
		#self.opener = urllib.FancyURLopener(proxies)
		self.getUrl()

	def OnAbout(self, event):
		#dlg = wx.MessageDialog(self, "This sample program shows off\n"
		#					  "frames, menus, statusbars, and this\n"
		#					  "message dialog.",
		#					  "About Me", wx.OK | wx.ICON_INFORMATION)
		#dlg.ShowModal()
		#dlg.Destroy()
		Form1(self.frame)


	def TimeToQuit(self, event):
		self.Close(True)

	
	def getUrl(self, data = ""):
		
		res = self.control.openUrl()
		self.parseXml(res) #.decode( "utf-8" )
	
	def parseXml(self, dat):
		toPage = self.xp.parseRegion(dat)
		
		
		self.control.SetPage(toPage)
		



if __name__ == '__main__':
	app = wx.App(False)  # Create a new app, don't redirect stdout/stderr to a window.
	frame = MyFrame(None, "M.video mobile app")
	app.MainLoop()
