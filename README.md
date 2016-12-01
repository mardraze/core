=== You can make your software 2 time faster.

Problems:
1. How to take parameter or service instance in Controller? In Command? In Service? In Twig Helper? In Event Listener?
 
Everywhere simmilar
```
$this->depedencies->getParameter('param_name');
```


2. SHow to send email faster?

```
$this->depedencies->sendEmail('email@example.com', 'AppBundle:Email:sample_email.html.twig', array('name' => 'Marcin'));
```


```
#AppBundle:Email:sample_email.html.twig'

{%block subject%}Example email to {{name}}{% endblock %}
{%block body%}Hello {{name}}{% endblock %}




```
# core
