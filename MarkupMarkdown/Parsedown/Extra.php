$replaceNode = $DOMDocument->firstChild->firstChild->firstChild;        if ($replaceNode !== null) {            $DOMDocument->replaceChild($replaceNode, $DOMDocument->firstChild);        }
