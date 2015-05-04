CodeMirror.defineMode("cdli", function () {
    var lineSyntax = function (stream, state) {
        if (state.firstLine) {
            state.firstLine = false;
            if (stream.match(/^&P\d{6} = /, true)) {
                return "keyword";
            } else {
                return "error";
            }
        }
        // check the format of text
        if (stream.match(/^\d+/, true)) {
            if (stream.match(/^\. [\x20-\x7E]*$/)) {
                return "string";
            } else {
                return "error";
            }
        }
        // check metadata
        if (stream.match('@', true)) {
            return "meta";
        }
        if (stream.match(/^\$|(>>)|#/, true)) {
            return "comment";
        }
        return "error";
    }
    var lastBeforeEOL = function (stream) {
        if (!stream.eol()) {
            stream.next();
            var lastBeforeEOL = stream.eol();
            stream.backUp(1);
            return lastBeforeEOL;
        }
        return false;
    }
    return {
        startState: function () {
            return {firstLine: true, curLineType: null};
        },
        token: function (stream, state) {
            if (stream.sol()) {
                // check the format of the line
                state.curLine_type = lineSyntax(stream, state);
                if (stream.eol()) {
                    // backup to the last word before the eol
                    stream.backUp(1);
                }
                if (state.curLine_type == 'error') {
                    stream.skipToEnd();
                }
                return state.curLine_type;
            } else if (lastBeforeEOL(stream)) {
                // we are at the last char before EOL
                if (stream.next() == ' ') {
                    return state.curLineType;
                } else {
                    return "error";
                }
            } else {
                stream.next();
                return state.curLine_type;
            }
            return null;
        }
    };
});