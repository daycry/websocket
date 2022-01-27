  <h3 align="center">CodeIgniter 4 WebSocket Library</h3>

[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/donate?business=SYC5XDT23UZ5G&no_recurring=0&item_name=Thank+you%21&currency_code=EUR)

CodeIgniter WebSocket library. It allows you to make powerfull realtime applications by using Ratchet [Socketo.me](http://socketo.me) Websocket technology.

#### WebSocket Library for Codeigniter 3.x https://github.com/takielias/codeigniter-websocket

<!-- TABLE OF CONTENTS -->
## Table of Contents

* [Getting Started](#getting-started)
  * [Prerequisites](#prerequisites)
  * [Installation](#installation)
  * [Publishing](#publishing)
* [Usage](#usage)
  * [Authentication and callbacks](#authentication-and-callbacks)
* [Roadmap](#roadmap)
* [Contributing](#contributing)
* [License](#license)
* [Contact](#contact)
* [Acknowledgements](#acknowledgements)


<!-- GETTING STARTED -->
## Getting Started

This is an example of how you may give instructions on setting up your project locally.
To get a local copy up and running follow these simple example steps.

### Prerequisites

- PHP 7.2+
- CodeIgniter Framework (4.* recommended)
- Composer
- PHP sockets extension enabled

### Installation

```sh
composer require daycry/websocket
```
### Publishing Resource
You need to publish the resources for the default configuration
```sh
php spark websocket:publish
```

<!-- USAGE EXAMPLES -->
## Usage
First start CodeIgniter
```sh
php spark serve
```

If you run the server in a different port, follow the command below.
```sh
PHP spark serve --port=9092
```

**Finally start Websocket Server**
```sh
php public/index.php Websocket start
```

**WOW You made it !!!** :heavy_check_mark: 

Open two pages of your project on the following URL with different IDs :

**For default Port**
`http://localhost:8080/Websocket/user/1`
`http://localhost:8080/Websocket/user/2`

**For custom Port**
`http://localhost:9092/Websocket/user/1`
`http://localhost:9092/Websocket/user/2`

<!-- Authentication & callbacks -->
## Authentication and callbacks

There are few predefined callbacks, here's the list :

` auth, event, close, citimer, roomjoin, roomleave, roomchat, chat `

Please check Websocket.php controller To get the Defining example of various Callback Function

```sh
    public function start()
    {
        $ws = service('Websocket');
        $ws->set_callback('auth', array($this, '_auth'));
        $ws->set_callback('event', array($this, '_event'));
        $ws->run();
    }

    public function _auth($datas = null)
    {
        // Here you can verify everything you want to perform user login.

        return (!empty($datas->user_id)) ? $datas->user_id : false;
    }

    public function _event($datas = null)
    {
        // Here you can do everything you want, each time message is received 
        echo 'Hey ! I\'m an EVENT callback' . PHP_EOL;
    }

 ```   
Two Callback functions have been defined in the above example. First One is **auth** & the Second one is **event**.
 
###### 🔨🔨🔨 If you need to customize Callback function, Please check the Websocket.php config file in Your config directory.


<!-- CONTRIBUTING -->
## Contributing

Contributions are what makes the open source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request


<!-- LICENSE -->
## License

Distributed under the MIT License. See `LICENSE` for more information.

<!-- CONTACT -->
## Contact

Daycry - [@daycry9](https://twitter.com/daycry9) - [https://github.com/daycry](https://github.com/daycry)

<!-- ACKNOWLEDGEMENTS -->
## Acknowledgements
* [http://socketo.me](https://github.com/ratchetphp/Ratchet)
* [Websocket Client for PHP](https://github.com/Textalk/websocket-php)
* [Choose an Open Source License](https://choosealicense.com)
* [GitHub Pages](https://pages.github.com)
* [Animate.css](https://daneden.github.io/animate.css)
* [Loaders.css](https://connoratherton.com/loaders)
* [Smooth Scroll](https://github.com/cferdinandi/smooth-scroll)
* [Font Awesome](https://fontawesome.com)