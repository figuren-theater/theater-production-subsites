<!-- PROJECT LOGO -->
<br />
<div align="center">
  <a href="https://github.com/figuren-theater/theater-production-subsites">
    <img src="https://raw.githubusercontent.com/figuren-theater/logos/main/favicon.png" alt="figuren.theater Logo" width="100" height="100">
  </a>

  <h1 align="center">figuren.theater | Production Subsites</h1>

  <p align="center">
    allows to create sub-sites of productions, within the WordPress Multisite network for puppeteers - <a href="https://figuren.theater">figuren.theater</a>.
    <br /><br /><br />
    <a href="https://meta.figuren.theater/blog"><strong>Read our blog</strong></a>
    <br />
    <br />
    <a href="https://figuren.theater">See the network in action</a>
    •
    <a href="https://mein.figuren.theater">Join the network</a>
    •
    <a href="https://websites.fuer.figuren.theater">Create your own network</a>
  </p>
</div>

## About 


This is the long desc

* [x] *list closed tracking-issues or `docs` files here*
* [ ] Do you have any [ideas](https://github.com/figuren-theater/theater-production-subsites/issues/new) ?

## Background & Motivation

This plugin allow to create sub-sites (aka posts) of productions (that are not productions itself, so not a typical hierachical post_type), which are populated with pre-made query-block patterns, that work (almost) automatically, e.g.

- Images (all posts with post-format image and a shadow-tax relationship with the production)
- Videos (all posts with post-format video and a shadow-tax relationship with the production)
- Quotes (all posts with post-format quote and a shadow-tax relationship with the production)
- Press releases (all posts with some defined post_tag (or cat.) and a shadow-tax relationship with the production)
- a booking Form for that production
- An auto-generated list of customers, based on played past events
- Technical rider (needs explicit editing, no automation planned here)
- a Rating-Kiosk to be shown directly after a show to motivate the audience to rate the production at connected services or within the own site
- Announcement-Kiosk which highlights the upcoming 3 events and some testimonials (quote-posts or rate-comments) to be shown within a store window or similar

## Install

1. Install via command line
    ```sh
    composer require figuren-theater/theater-production-subsites
    ```


## Built with & uses

  - [dependabot](/.github/dependabot.yml)
  - [code-quality](https://github.com/figuren-theater/code-quality/)
     
     A set of status checks to ensure high and consitent code-quality for the figuren.theater platform.
  - [johnbillion/extended-cpts](https://github.com/johnbillion/extended-cpts)
     > A library which provides extended functionality to WordPress custom post types and taxonomies.

     Adds some nice enhancements to the registered `post_type`(s), but is *not* required.
  - ....

## Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request


## Versioning

We use [Semantic Versioning](http://semver.org/) for versioning. For the versions
available, see the [tags on this repository](https://github.com/figuren-theater/theater-production-subsites/tags).

## Authors

  - **Carsten Bach** - *Provided idea & code* - [figuren.theater/crew](https://figuren.theater/crew/)

See also the list of [contributors](https://github.com/figuren-theater/theater-production-subsites/contributors)
who participated in this project.

## License

This project is licensed under the **GPL-3.0-or-later**, see the [LICENSE](/LICENSE) file for
details

## Acknowledgments

  - [altis](https://github.com/search?q=org%3Ahumanmade+altis) by humanmade, as our digital role model and inspiration
  - [@roborourke](https://github.com/roborourke) for his clear & understandable [coding guidelines](https://docs.altis-dxp.com/guides/code-review/standards/)
  - [python-project-template](https://github.com/rochacbruno/python-project-template) for their nice template->repo renaming workflow
