MultiChain Demo
===================

MultiChain Web Demo is a simple web interface for [MultiChain](http://www.multichain.com/) blockchains, written in PHP.

https://github.com/MultiChain/multichain-web-demo

    Copyright(C) 2015,2016 by Coin Sciences Ltd.
    License: GNU Affero General Public License, see the file LICENSE.txt.


Welcome to MultiChain Web Demo
==============================

This software uses PHP to provide a web front-end for a [MultiChain](http://www.multichain.com/) blockchain node.

System Requirements
-------------------

* A computer running web server software such as Apache.
* PHP 5.x or later with the `curl` extension.
* MultiChain 1.0 alpha 26 or later.

**Note that this Web Demo does not yet support MultiChain 2.0 preview releases.**


Create and launch a MultiChain Blockchain
-----------------------------------------

If you do not yet have a chain to work with, [Download MultiChain](http://www.multichain.com/download-install/) to install MultiChain and create a chain named `chain-demo` as follows:

    multichain-util create chain-demo
    multichaind chain-demo -daemon
    
If your web server is running on the same computer as `multichaind`, you can skip the rest of this section. Otherwise:

    multichain-cli chain-demo stop

Then add this to `~/.multichain/chain-demo/multichain.conf`:

    rpcallowip=[IP address of your web server]
    ex: rpcallowip=127.0.0.1
  
Then start MultiChain again:
  
    multichaind chain-demo -daemon



Configure the Web Demo
----------------------

_This section assumes your blockchain is named `chain-demo` and you are running the node and web server on a Unix variant such as Linux. If not, please substitute accordingly._

Make your life easy for the next step by running these on the node's server:

    cat ~/.multichain/chain-demo/multichain.conf
    grep rpc-port ~/.multichain/chain-demo/params.dat
    
In the web demo directory, copy the `config-example.txt` file to `config.txt`:

	cp config-example.txt config.txt
  
In the demo website directory, enter chain details in `config.txt` e.g.:

    default.name=Default                # name to display in the web interface
    default.rpchost=127.0.0.1           # IP address of MultiChain node
    default.rpcport=12345               # usually default-rpc-port from params.dat
    default.rpcuser=multichainrpc       # username for RPC from multichain.conf
    default.rpcpassword=mnBh8aHp4mun... # password for RPC from multichain.conf

Multiple chains are supported by the web demo by copying the same section again but with different prefixes before the period, for example:

	another.name=...
	another.rpchost=...
	...

**Note that the `config.txt` file is readable by users of your web demo installation, and contains your MultiChain API password, so you should never use this basic setup for a production system.**


Launch the Web Demo
-------------------

No additional configuration or setup is required. Based on where you installed the web demo, open the appropriate address in your web browser, and you are ready to go!
