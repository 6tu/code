
#<pre>
#proxy.py

#-------------------------------------------------
try:
    import wsgiref.handlers
    from google.appengine.api.urlfetch import fetch
    from google.appengine.ext import webapp
except:
    class WEBAPP:pass
    webapp = WEBAPP()
    webapp.RequestHandler = object
    pass

import sys
_myurl = ''

def _encodeurl(url):
    return url.encode('base64')

def _decodeurl(url):
    try:
        return url.decode('base64')
    except:
        return ''
#20
def _buildurl(curr_url,url,request):
    from urlparse import urljoin
    from urllib import urlencode
    if url.startswith('//'):
        url = 'http:'+url
    newurl = urljoin(curr_url,url)
    ll = newurl.lower()
    if not ll.startswith('http://') and not ll.startswith('https://'):
        return newurl
    param = [('q',_encodeurl(newurl))] 
    return '%s?%s'%(_myurl,urlencode(param)) 

class FormHandler(webapp.RequestHandler):
    @staticmethod
    def get(self,*arg):
        form = """
        <form action="#" method="post">
        url:<input name="url" cols="100"/>
        <input type="submit"/>
        </form>
        """
#41
        html = '<html><head></head><body>%s</body></html>'%(form)
        req = self.request
        import sys
        print >>sys.stderr,(_buildurl('http://'+req.headers['host'],url,req))
        self.response.out.write(html)

    @staticmethod
    def post(self,*arg):
        req = self.request
        url = req.POST['url']
        if not url:
            self.error(500)
        if not url.lower().startswith('http'):
            url = 'http://'+url

        self.redirect(_buildurl('http://'+req.headers['host'],url,req))
        pass

class ProxyHandler(webapp.RequestHandler):
    @staticmethod
    def get(self,*arg):
        from urllib import unquote
        req = self.request
        resp = self.response
        if not req.GET['q']:
            self.error(500)
        url = req.GET['q']
        url = unquote(url)
        url = _decodeurl(url)
        if url=='':
            self.error(501)
            resp.out.write('error get url')
            return
        ProxyHandler.visit(self,url)

    post = get

    @staticmethod
    def visit(self,url):
        from urlparse import urlparse
        req = self.request
        resp = self.response
        headers = {'Host':urlparse(url)[1]}
        for k,v in req.headers.items():
            if k.lower() not in ['host','referer','accept-encoding','content-length','content-type']:
                headers[k]=v
        #here forward referer
        if 'referer' in req.headers:
            ref = req.headers['referer']
            qs = urlparse(ref)[4]
            from cgi import parse_qs
            d = parse_qs(qs)
            if 'q' in d:
                q = d['q'][0]
                ref = _decodeurl(q)
                headers['Referer'] = ref
                print >>sys.stderr,"\t send Referer:",ref

        print >>sys.stderr,'fetch:',url,req.method
        print >>sys.stderr,'headers:'
        for k,v in headers.items():
            print >>sys.stderr,k,v

        newresp = fetch(url,req.body,req.method,headers)
        _hoppish = {
            'connection':1, 'keep-alive':1, 'proxy-authenticate':1,
            'proxy-authorization':1, 'te':1, 'trailers':1, 'transfer-encoding':1,
            'upgrade':1
        }.has_key

        for k,v in newresp.headers.items():
            if not _hoppish(k.lower()):
                resp.headers[k]=v
        content = newresp.content
        print >>sys.stderr,'content-type:',newresp.headers.get('content-type','')
        if newresp.headers.get('content-type','').find('text/html')>=0:
            import proxify
            content=proxify.proxify_html(content,lambda u:_buildurl(url,u,req))
        resp.out.write(content)

class Handler(webapp.RequestHandler):
    def get(self,*arg):
        req = self.request
        realh = None
        if req.query_string == '':
            realh = FormHandler
        elif req.GET['q']:
            realh = ProxyHandler
        else:
            self.error(500)
        f = getattr(realh,req.method.lower())
        f(self,arg)
        pass
    post = get


#-------------------------------------------------
#proxify.py


#-------------------------------------------------


# coding=utf8
import re
import sys
_re_parse_attrstring=re.compile("""([a-zA-Z\-\/]+)\s*(?:=\s*(?:\"([^\">]*)\"?|'([^'>]*)'?|([^'\"\s]*)))?""")
def parse_attrstring(attrstring):
    "return a dict for each attribute pair.dict['disabled']=True for no value attribute."
    alls = _re_parse_attrstring.findall(attrstring)
    attrs = {}
    for i in alls:
        v = i[3] or i[2] or i[1] or True
        attrs[i[0].lower()] = v
    return attrs

def unparse_attrstring(attr):
    attrstring = []
    app = attrstring.append
    for k,v in attr.items():
        if v is True:
            app(k)
        else:
            app('%s="%s"'%(k,v))
    return ' '.join(attrstring)
_url_tag_attr = {
    'a'         :('href',),
    'img'       :('src', 'longdesc'),
    'image'     :('src', 'longdesc'),
    'body'      :('background',),
    'base'      :('href',),
    'frame'     :('src', 'longdesc'),
    'iframe'    :('src', 'longdesc'),
    'head'      :('profile',),
    'layer'     :('src',),
    'input'     :('src', 'usemap'),
    'form'      :('action',),
    'area'      :('href',),
    'link'      :('href', 'src', 'urn'),
    'meta'      :(),
    'param'     :('value',),
    'applet'    :('codebase', 'code', 'object', 'archive'),
    'object'    :('usermap', 'codebase', 'classid', 'archive','data'),
    'script'    :('src',),
    'select'    :('src',),
    'hr'        :('src',),
    'table'     :('background',),
    'tr'        :('background',),
    'th'        :('background',),
    'td'        :('background',),
    'bgsound'   :('src',),
    'blockquote':('cite',),
    'del'       :('cite',),
    'embed'     :('src',),
    'fig'       :('src', 'imagemap'),
    'ilayer'    :('src',),
    'ins'       :('cite',),
    'note'      :('src',),
    'overlay'   :('src', 'imagemap',),
    'q'         :('cite',),
    'ul'        :('src',)
}
for i in _url_tag_attr.values():
    assert type(i)==tuple

#return False for no modify
def _proxify_attr(tag,attrdict,proxify_url):
    attrs_tp = _url_tag_attr.get(tag.lower(),())
    modified = False
    #url
    for attr in attrs_tp:
        v = attrdict.get(attr,None)
        if v is not None:
            if v is not True:
                modified = True
                attrdict[attr] = proxify_url(v)
    #inline css
    v = attrdict.get('style',None)
    if v is not None and v is not True:
        newv =  _proxify_inline_css(v,proxify_url)
        if newv != v:
            modified = True
            attrdict['style'] = newv
    #meta refresh
    if tag=='meta' and ('content' in attrdict)\
        and ('http-equiv' in attrdict)\
        and attrdict['http-equiv'] is not True\
        and attrdict['content'] is not True\
        and attrdict['http-equiv'].strip().lower()=='refresh':
            modified = True
            attrdict['content'] = proxify_url(attrdict['content'])
    #tag object todo
    #tag applet todo
    #tag param todo
    return modified

_re_inlinecss_url = re.compile('url\s*\(([^\(\)]+)\)')
def _proxify_inline_css(css,proxify_url):
    lastpos = 0
    newcss = []
    app = newcss.append
    for m in re.finditer(_re_inlinecss_url,css):
        app(css[lastpos:m.start()])
        url = m.groups()[0]
        if url.find('"')>=0:
            delma = '"'
        elif url.find("'")>=0:
            delma = "'"
        else:
            delma = ''
        url = url.strip('\'"')
        url = proxify_url(url)
        app('url(%s%s%s)'%(delma,url,delma))
        lastpos = m.end()
    app(css[lastpos:len(css)])
    return ''.join(newcss)

def proxify_css(css,proxify_url):
    return css

#return string for new tag
def proxify_tag(tagstring,tag,attrstring,proxify_url):
    attrdict = parse_attrstring(attrstring)
    #if not mofidy,then return the old tagstring
    if _proxify_attr(tag,attrdict,proxify_url):
        return '<%s %s>'%(tag,unparse_attrstring(attrdict))
    return tagstring

_re_tag = re.compile(r'<\s*([a-zA-Z\?-]+)([^>]*)>')
def proxify_html_without_script(html,proxify_url):
    newhtml = []
    #matchs = _re_tag.findall(html)
    lastpos = 0
    app = newhtml.append
    for m in re.finditer(_re_tag,html):
        app(html[lastpos:m.start()])
        app(proxify_tag(m.group(),m.groups()[0],m.groups()[1],proxify_url))
        lastpos = m.end()
    app(html[lastpos:])
    return ''.join(newhtml)

def proxify_html(html,proxify_url):
    start = '<script'
    end = '</script>'
    allpos = []
    curr_pos = 0
    low_html = html.lower()
    htmls = []
    while True:
        p1=low_html.find(start,curr_pos)
        if p1<0:break
        p2=low_html.find(end,p1+len(start))
        if p2<0:break
        p2 += len(end)
        allpos.append((p1,p2))
        curr_pos = p2
    last = 0
    for p1,p2 in allpos:
        htmls.append( proxify_html_without_script(html[last:p1],proxify_url) )
        htmls.append(html[p1:p2])
        last = p2
    htmls.append( proxify_html_without_script(html[last:],proxify_url) )
    return ''.join(htmls)
#test code
sample_html="""<script language='js'><a href="link"></script>
111
<html>
<head></head>
<a href=link>
link2 </a>
<a href="http://www.baidu.com" t="s"/>
</html>
<a class="i" target="_blank" href="fonying">
<img height="78" width="78" onload="DownImage(this,78,78,-1,1);"src="http://himg.baidu.com/sys/portrait/item/5840666f6e79696e670701.jpg"/>
</a><script><a href="link"></script>
<style>stst</style>
<script><a href="link"></script>""".decode('utf8')
if __name__=='__main__':
    def purl(url):
        return 'http:proxy?'+url
    print proxify_html(sample_html,purl).encode('gbk')
    print _proxify_inline_css('height:50;url (http://www.baidu.com);a=url("link")',purl)
    print _proxify_inline_css('height:50',purl)
#</pre>