// 用法 string.toUTF16()

String.prototype.toUTF16 = function() {
    var str = this;
    if ((str.match(/^[\x00-\x7f]*$/) != null) || (str.match(/^[\x00-\xff]*$/) == null)) {
        return str.toString();
    }
    var out, i, j, len, c, c2, c3, c4, s;
    out = [];
    len = str.length;
    i = j = 0;
    while (i < len) {
        c = str.charCodeAt(i++);
        switch (c >> 4) {
        case 0:
        case 1:
        case 2:
        case 3:
        case 4:
        case 5:
        case 6:
        case 7:
            // 0xxx xxxx
            out[j++] = str.charAt(i - 1);
            break;
        case 12:
        case 13:
            // 110x xxxx   10xx xxxx
            c2 = str.charCodeAt(i++);
            out[j++] = String.fromCharCode(((c & 0x1f) << 6) | (c2 & 0x3f));
            break;
        case 14:
            // 1110 xxxx  10xx xxxx  10xx xxxx
            c2 = str.charCodeAt(i++);
            c3 = str.charCodeAt(i++);
            out[j++] = String.fromCharCode(((c & 0x0f) << 12) | ((c2 & 0x3f) << 6) | (c3 & 0x3f));
            break;
        case 15:
            switch (c & 0xf) {
            case 0:
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
                // 1111 0xxx  10xx xxxx  10xx xxxx  10xx xxxx
                c2 = str.charCodeAt(i++);
                c3 = str.charCodeAt(i++);
                c4 = str.charCodeAt(i++);
                s = ((c & 0x07) << 18) | ((c2 & 0x3f) << 12) | ((c3 & 0x3f) << 6) | (c4 & 0x3f) - 0x10000;
                if (0 <= s && s <= 0xfffff) {
                    out[j++] = String.fromCharCode(((s >>> 10) & 0x03ff) | 0xd800, (s & 0x03ff) | 0xdc00);
                } else {
                    out[j++] = "?";
                }
                break;
            case 8:
            case 9:
            case 10:
            case 11:
                // 1111 10xx  10xx xxxx  10xx xxxx  10xx xxxx  10xx xxxx
                i += 4;
                out[j++] = "?";
                break;
            case 12:
            case 13:
                // 1111 110x  10xx xxxx  10xx xxxx  10xx xxxx  10xx xxxx  10xx xxxx
                i += 5;
                out[j++] = "?";
                break;
            }
        }
    }
    return out.join("");
}
