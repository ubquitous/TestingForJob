


class File extends React.Component{
    
    constructor(){
        super();
        this.state = {
            delete: undefined,
            update: undefined,
            insert: undefined,
            countPage: 0,
            data: undefined
        }
        send(this, "function/View.php");

    }
    getFile = async(e) => {
        e.preventDefault();
        var it = this;
        handleFiles(e.target.elements.file.files);
        function handleFiles(files) {
            ([...files]).forEach(uploadFile);
        }
        function uploadFile(file) {
            let url = document.getElementById("file_form").getAttribute("action");
            let formData = new FormData();
            formData.append('file', file);
            send(it, url, formData);
            send(it, "function/View.php");
    
        }

        
    }
    changeFile = async(e) => {
        document.getElementById("custom-file").innerHTML = "Выбран файл: "+e.target.files[0].name;
    }
    render(){
        return ( 
            <main>
                <form onSubmit={this.getFile} id="file_form" action="function/getJSON.php">
                    <label for="file" id="custom-file" class="custom-file">Выбрать файл</label>
                    <input type = "file" id="file" onChange={this.changeFile} name = "file"/>
                    <button>Загрузить</button>
                    <Info 
                        delete={this.state.delete}
                        update={this.state.update}
                        insert={this.state.insert}/>
                </form>
                <Table
                        countPage={this.state.countPage}
                        data={this.state.data}/>
            </main>
        )
    }
}

function send(it, url, formData=""){
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(function(data) {
        data.text().then(function(res) {
            console.log(res);
            let dataArr=JSON.parse(res);
            if(     dataArr["delete"]!=undefined
                 || dataArr["update"]!=undefined
                 || dataArr["insert"]!=undefined
            ){
                    it.setState({
                        delete: dataArr["delete"],
                        update: dataArr["update"],
                        insert: dataArr["insert"]
                    })
            }
            if(dataArr["page"]!=undefined && dataArr["data"]!=undefined){
                it.setState({
                    countPage: parseInt(dataArr["page"]),
                    data: dataArr["data"]
                })
            }else{
                if(dataArr["page"]!=undefined){
                    it.setState({
                        countPage: parseInt(dataArr["page"])
                    })
                }
                if(dataArr["data"]!=undefined){
                    it.setState({
                        data: dataArr["data"]
                    })
                }
            }
        });
    })
    .catch((er) => {
        console.log(er);
        er.text().then(function(res) {
            dataArr=[];
            console.log("Error!!!");
        });
    })
}



class Info extends React.Component{
    render(){
        return (
                <div className="info">
                        <div>
                            <p>Удалено: {this.props.delete}</p>
                            <p>Изменено: {this.props.update}</p>
                            <p>Добавлено: {this.props.insert}</p>
                        </div>
               </div>
        )
    }
} 


class Table extends React.Component{
   
    constructor(){
        super();
        this.state = {
            currentPage:1,
            data: undefined,
            countPage: 0,
            bound: 10,
            data:undefined,
            sort: 'name'
        }
        send(this, "function/View.php");
        var formData = new FormData();
        formData.append('sort', this.state.sort);
        formData.append('page', this.state.currentPage - 1);
        send(this, "function/View.php", formData);
        this.handleClick = this.handleClick.bind(this);
        this.sort = this.sort.bind(this);
        this.btnNextClick = this.btnNextClick.bind(this);
        this.btnPrevClick = this.btnPrevClick.bind(this);


    }
    handleClick(event) {
        let listid = Number(event.target.id);
        this.setState({
            currentPage: listid
        });
        var formData = new FormData();
        formData.append('sort', this.state.sort);
        formData.append('page', listid - 1);
        send(this, "function/View.php", formData);
        $(".pagination button.active").removeClass('active');
        $('.pagination button#'+listid).addClass('active');
    }
    componentDidUpdate() {
        $(".pagination button.active").removeClass('active');
        $('.pagination button#'+this.state.currentPage).addClass('active');
    }
    btnPrevClick() {
        let listid = this.state.currentPage - 1;
        this.setState({ currentPage : listid});
    }
    btnNextClick() {
        let listid = this.state.currentPage + 1;
        this.setState({ currentPage : listid});
    }

    sort(event){
        let name = event.target.id;
        let sort = this.state.sort;
        this.state.currentPage = 1;
        sort = sort == name ? name + "_down" : name;
        this.setState({
            currentPage: 1,
            sort: sort
        })
        var formData = new FormData();
        formData.append('sort', sort);
        formData.append('page', 0);
        send(this, "function/View.php", formData);
    }

    render(){
        if(this.state.countPage != this.props.countPage){
             this.state.countPage = this.props.countPage;
             this.state.currentPage = 1;
             this.state.data = this.props.data;
        }
        var page = this.state.currentPage+this.state.bound / 2 > this.state.countPage 
                        ? this.state.countPage - this.state.bound 
                        : this.state.currentPage - this.state.bound / 2;
        page = page < 1 ? 1 : page;
        // Logic for displaying page numbers
        const pageNumbers = [];
        for (var i = page; i <= page + this.state.bound; i++) {
          if(i<=this.state.countPage)  
            pageNumbers.push(i);
        }   
        const renderPageNumbers = pageNumbers.map(number => {
          return (
            <div>
                    <button
                        key={number}
                        id={number}
                        onClick={this.handleClick}
                        >
                        {number}
                    </button>
            </div>
          );
        });
        if(this.state.data == undefined){
            this.state.data = JSON.parse('[{"name":"","email":""}]');
        }
            const renderTable = this.state.data.map(info => {
                return (
                <tr>
                        <td>{info["name"]}</td>
                        <td>{info["email"]}</td>
                </tr>
                );
            });
        return(
            <div className="main">
                <table key={Math.random()}>
                    <tbody key={Math.random()}>
                        <tr>
                            <th onClick={this.sort} key="name" id="name" data-sort="name">Имя</th>
                            <th onClick={this.sort} key="email" id="email">Почта</th>
                        </tr>
                        {renderTable}
                    </tbody>
                </table> 
                <div className="pagination">
                    {renderPageNumbers}
                </div>
            </div>
        )
    }
}


ReactDOM.render(
    <File/>,
  document.getElementById("content")
);