# Discovery service for SHACL shapes

Example implementation of a discovery service for SHACL shapes/shape groups. It is written in PHP and based on [Silex](https://silex.sensiolabs.org/) and [Twig](https://twig.sensiolabs.org/). The RDF part is implemented using [Saft](https://github.com/SaftIng/Saft).

![](assets/screenshot.png)

## Contributions welcome

If you want to participate, just fork this repository! Or open an issue here. Or send a mail at [public-shacl@w3.org](mailto:public-shacl@w3.org).

## Run docker container

We provide a docker container, which contains the runtime environment.

**Build**:

```
make build
```

**Run**:

```
make
```

Afterwards, the website should be reachable using `http://localhost:7000/web`.

## License

[MIT](LICENSE)
