const W2 = function () {
    class W2Error extends Error{

    }

    class W2HtmlUtils {
        static clearContent(node){
            while(node.firstChild){
                node.removeChild(node.firstChild)
            }
        }

        static getExistingNode(info){
            if(info instanceof Element || info instanceof Text){
                return info
            } else if(info instanceof String || typeof info === "string"){
                const node = document.getElementById(info)

                if(node) return node
            }

            throw new W2Error("Could not find element!")
        }

        static isDOMNode(node){
            return node instanceof Element || node instanceof Text || node instanceof DocumentFragment || node instanceof Comment
        }
    }

    class W2HtmlNode {
        static new(data){
            let node
            if(W2HtmlUtils.isDOMNode(data)){
                node = data
            } else if (data.domNode && W2HtmlUtils.isDOMNode(data.domNode)){
                node = data.domNode
            } else if(data instanceof String || typeof data === "string"){
                node = document.createElement(data)
            } else if(data instanceof Function){
                return W2HtmlNode.new(data())
            } else if(!data){
                node = document.createDocumentFragment()
            } else {
                throw new W2Error(`Could not create element from type '${typeof data}'!`)
            }

            return new W2HtmlNode(node)
        }

        static empty(){
            return new W2HtmlNode(document.createDocumentFragment())
        }

        domNode

        constructor(domNode) {
            this.domNode = domNode
        }

        content(...args){
            for (const arg of args) {
                if(W2HtmlUtils.isDOMNode(arg)){
                    this.#addChildNode(arg)
                } else if (arg.domNode && W2HtmlUtils.isDOMNode(arg.domNode)){
                    this.#addChildNode(arg.domNode)
                } else if(arg instanceof String || typeof arg === "string"){
                    this.#addChildNode(document.createTextNode(arg))
                } else if(arg instanceof Number || typeof arg === "number"){
                    this.#addChildNode(document.createTextNode(arg.toString()))
                } else if(arg instanceof Function || typeof arg === "function"){
                    this.content(arg())
                } else if(arg instanceof Array){
                    this.content(...arg)
                } else if(arg && arg.toString) {
                    this.#addChildNode(document.createTextNode(arg.toString()))
                }
            }

            return this
        }

        class(...names){
            if (this.domNode instanceof Element){
                for (const name of names) {
                    this.domNode.classList.add(name)
                }
            }

            return this
        }

        style(property, value){
            this.domNode.style.setProperty(property,value)

            return this
        }

        addInto(target){
            const node = W2HtmlUtils.getExistingNode(target)
            W2HtmlUtils.clearContent(node)

            node.appendChild(this.domNode)

            return this
        }

        event(name,callback){
            this.domNode.addEventListener(name,callback)
            return this
        }

        #addChildNode(node){
            this.domNode.appendChild(node)
        }
    }

    class W2Mount {
        render
        anchor
        last
        domNode

        constructor(render,domNode,anchor,last) {
            this.render = render
            this.domNode = domNode
            this.anchor = anchor
            this.last = last
        }

        update(){
            if(this.last) {
                //remove old dom
                let currentNode = this.anchor.nextSibling
                while (currentNode) {
                    currentNode.parentNode.removeChild(currentNode)

                    if (currentNode === this.last) break

                    currentNode = currentNode.nextSibling
                }
            }

            const newTree = W2HtmlNode.empty()
            this.render(newTree)

            this.last = newTree.domNode.lastChild

            this.anchor.parentNode.insertBefore(newTree.domNode,this.anchor.nextSibling)
        }
    }

    const mount = (render)=>{
        const anchor = document.createComment("mount anchor")

        const root = W2HtmlNode.empty()
        render(root)

        const last = root.domNode.lastChild
        const container = W2HtmlNode.empty().content(anchor).content(root)

        return new W2Mount(render,container.domNode,anchor,last)
    }

    class W2Component {

        #mount

        stateChanged(){
            if(this.#mount){
                this.#mount.update()
            }
        }

        mount(){
            this.#mount = mount((...args)=>this.render(...args))
            return this.#mount
        }

        render(){
            throw W2Error("You need to implement the render method on a component!")
        }
    }

    return {
        html: W2HtmlNode.new,
        mount,
        W2HtmlUtils,
        W2Error,
        W2Component
    }
}()

class TestButtonComponent extends W2.W2Component{
    counter

    constructor() {
        super();
        this.counter = 0
    }

    buttonClicked(){
        this.counter += 1
        this.stateChanged()
    }

    render(container){
        container.content(W2.html("button")
            .content("Counter: ",this.counter)
            .event("click",()=>this.buttonClicked())
        )
    }
}

let counter = 0
let buttonComponent = new TestButtonComponent()

const h1 = W2.html("h1")
    .content("Title","text",[" ","!"])

const testMount = W2.mount((container)=>{
    container.content(W2.html("p").content(counter))
})

const button = W2.html("button")
    .content("click")
    .event("click",()=>{
        console.log("asd")
        counter++
        testMount.update()
    })

const div = W2.html("div")
    .content(h1)
    // .content(W2.html("p").content("paragraph"))
    .content(testMount)
    .content(button)
    .content(buttonComponent.mount())
    .addInto("target")
